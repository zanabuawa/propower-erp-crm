<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ReceiptForm extends Component
{
    public PurchaseOrder $order;
    public ?int $warehouse_id = null;
    public string $notes = '';
    public array $items = [];

    public function mount($order): void
    {
        $this->order = $order instanceof PurchaseOrder
            ? $order
            : PurchaseOrder::with(['items.product'])->findOrFail($order);

        $defaultWarehouse = auth()->user()->branch_id
            ? Warehouse::where('branch_id', auth()->user()->branch_id)
                ->where('is_active', true)
                ->first()?->id
            : null;

        $this->warehouse_id = $defaultWarehouse;

        $this->items = $this->order->items
            ->filter(fn($i) => $i->pending_quantity > 0)
            ->map(fn($i) => [
                'order_item_id'    => $i->id,
                'product_id'       => $i->product_id,
                'product_name'     => $i->description,
                'purchase_price'   => $i->unit_price,
                'warehouse_id'     => $defaultWarehouse,
                'quantity_ordered'  => $i->quantity,
                'quantity_received' => $i->pending_quantity,
                'quantity_pending'  => $i->pending_quantity,
                'operational_cost' => '0',
                'notes'            => '',
            ])->values()->toArray();
    }

    public function rules(): array
    {
        return [
            'warehouse_id'               => 'required|exists:warehouses,id',
            'items'                      => 'required|array|min:1',
            'items.*.quantity_received'  => 'required|numeric|min:0',
            'items.*.warehouse_id'       => 'required|exists:warehouses,id',
            'items.*.operational_cost'   => 'required|numeric|min:0',
        ];
    }

    public function updatedWarehouseId(): void
{
    foreach ($this->items as $index => $item) {
        $this->items[$index]['warehouse_id'] = $this->warehouse_id;
    }
}

    public function save(): void
    {

        $this->validate();

        DB::transaction(function () {
            $folio = 'REC-' . str_pad(
                PurchaseReceipt::where('company_id', auth()->user()->company_id)->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $receipt = PurchaseReceipt::create([
                'company_id' => auth()->user()->company_id,
                'purchase_order_id' => $this->order->id,
                'received_by' => auth()->id(),
                'warehouse_id' => $this->warehouse_id,
                'folio' => $folio,
                'status' => 'completed',
                'notes' => $this->notes,
                'received_at' => now(),
            ]);

            foreach ($this->items as $item) {
                if ($item['quantity_received'] <= 0)
                    continue;

                $receipt->items()->create([
                    'purchase_order_item_id' => $item['order_item_id'],
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity_received' => $item['quantity_received'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Actualizar cantidad recibida en la orden
                $orderItem = PurchaseOrderItem::find($item['order_item_id']);
                $orderItem->increment('quantity_received', $item['quantity_received']);

                // Actualizar stock y costos del producto
                if ($item['product_id']) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id']],
                        ['quantity' => 0]
                    );
                    $quantityBefore = $stock->quantity;
                    $stock->increment('quantity', $item['quantity_received']);

                    // Actualizar precio de obtención y gastos de operación en el producto
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $newPurchasePrice = (float) $item['purchase_price'];
                        $newOpCostPct     = (float) ($item['operational_cost'] ?? 0); // porcentaje
                        $profitMargin     = (float) $product->profit_margin;

                        // Los gastos de operación se reemplazan (no acumulan): son el % actual de esta recepción
                        // sale_price = costo * (1 + margen% / 100)
                        $newSalePrice = round($newPurchasePrice * (1 + $profitMargin / 100), 2);

                        $product->update([
                            'purchase_price'    => $newPurchasePrice,
                            'operational_costs' => $newOpCostPct,
                            'sale_price'        => $newSalePrice,
                        ]);
                    }

                    // Registrar movimiento de stock
                    $movement = StockMovement::create([
                        'company_id'  => auth()->user()->company_id,
                        'warehouse_id' => $item['warehouse_id'],
                        'user_id'     => auth()->id(),
                        'type'        => 'entry',
                        'folio'       => $folio,
                        'status'      => 'confirmed',
                        'reference'   => $this->order->folio,
                        'notes'       => 'Recepción de orden de compra',
                        'moved_at'    => now(),
                    ]);

                    $movement->items()->create([
                        'product_id'        => $item['product_id'],
                        'warehouse_id'      => $item['warehouse_id'],
                        'quantity'          => $item['quantity_received'],
                        'unit_price'        => $item['purchase_price'] ?? 0,
                        'quantity_before'   => $quantityBefore,
                        'quantity_after'    => $stock->fresh()->quantity,
                    ]);
                }
            }

            // Actualizar estado de la orden
            $order = $this->order->fresh()->load('items');
            $allReceived = $order->items->every(fn($i) => $i->quantity_received >= $i->quantity);
            $anyReceived = $order->items->some(fn($i) => $i->quantity_received > 0);

            if ($allReceived) {
                $order->update(['status' => 'received']);
            } elseif ($anyReceived) {
                $order->update(['status' => 'partial_received']);
            }
        });

        session()->flash('success', 'Recepción registrada y stock actualizado.');
        $this->redirect(route('purchases.orders.show', $this->order));
    }

    public function render()
    {
        return view('livewire.purchases.receipt-form', [
            'warehouses' => Warehouse::where('is_active', true)->with('branch')->orderBy('name')->get(),
        ]);
    }
}