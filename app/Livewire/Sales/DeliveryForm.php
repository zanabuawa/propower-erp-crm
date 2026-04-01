<?php

namespace App\Livewire\Sales;

use App\Models\SaleDelivery;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DeliveryForm extends Component
{
    public SaleOrder $order;
    public ?int $warehouse_id = null;
    public string $notes = '';
    public array $items = [];

    public function mount($order): void
    {
        $this->order = $order instanceof SaleOrder
            ? $order
            : SaleOrder::with(['items.product'])->findOrFail($order);

        $defaultWarehouse = auth()->user()->branch_id
            ? Warehouse::where('branch_id', auth()->user()->branch_id)
                ->where('is_active', true)->first()?->id
            : null;

        $this->warehouse_id = $defaultWarehouse;

        $this->items = $this->order->items
            ->filter(fn($i) => $i->pending_quantity > 0)
            ->map(fn($i) => [
                'order_item_id'   => $i->id,
                'product_id'      => $i->product_id,
                'product_name'    => $i->description,
                'warehouse_id'    => $defaultWarehouse,
                'quantity_pending' => $i->pending_quantity,
                'quantity'        => $i->pending_quantity,
            ])->values()->toArray();
    }

    public function updatedWarehouseId(): void
    {
        foreach ($this->items as $index => $item) {
            $this->items[$index]['warehouse_id'] = $this->warehouse_id;
        }
    }

    public function rules(): array
    {
        return [
            'warehouse_id'            => 'required|exists:warehouses,id',
            'items'                   => 'required|array|min:1',
            'items.*.quantity'        => 'required|numeric|min:0',
            'items.*.warehouse_id'    => 'required|exists:warehouses,id',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $folio = 'REM-' . str_pad(
                SaleDelivery::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $delivery = SaleDelivery::create([
                'company_id'    => auth()->user()->company_id,
                'sale_order_id' => $this->order->id,
                'customer_id'   => $this->order->customer_id,
                'created_by'    => auth()->id(),
                'warehouse_id'  => $this->warehouse_id,
                'folio'         => $folio,
                'status'        => 'delivered',
                'notes'         => $this->notes,
                'delivered_at'  => now(),
            ]);

            foreach ($this->items as $item) {
                if ($item['quantity'] <= 0) continue;

                $delivery->items()->create([
                    'sale_order_item_id' => $item['order_item_id'],
                    'product_id'         => $item['product_id'],
                    'warehouse_id'       => $item['warehouse_id'],
                    'quantity'           => $item['quantity'],
                ]);

                // Actualizar cantidad entregada
                $orderItem = SaleOrderItem::find($item['order_item_id']);
                $orderItem->increment('quantity_delivered', $item['quantity']);

                // Descontar stock
                if ($item['product_id']) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id']],
                        ['quantity' => 0]
                    );
                    $quantityBefore = $stock->quantity;
                    $stock->decrement('quantity', $item['quantity']);

                    $movement = StockMovement::create([
                        'company_id'   => auth()->user()->company_id,
                        'warehouse_id' => $item['warehouse_id'],
                        'user_id'      => auth()->id(),
                        'type'         => 'exit',
                        'folio'        => $folio,
                        'status'       => 'confirmed',
                        'reference'    => $this->order->folio,
                        'notes'        => 'Remisión de orden de venta',
                        'moved_at'     => now(),
                    ]);

                    $movement->items()->create([
                        'product_id'      => $item['product_id'],
                        'warehouse_id'    => $item['warehouse_id'],
                        'quantity'        => $item['quantity'],
                        'unit_price'      => 0,
                        'quantity_before' => $quantityBefore,
                        'quantity_after'  => $stock->fresh()->quantity,
                    ]);
                }
            }

            // Actualizar estado de la orden
            $order = $this->order->fresh()->load('items');
            $allDelivered = $order->items->every(fn($i) => $i->quantity_delivered >= $i->quantity);
            $anyDelivered = $order->items->some(fn($i) => $i->quantity_delivered > 0);

            if ($allDelivered) {
                $order->update(['status' => 'delivered']);
            } elseif ($anyDelivered) {
                $order->update(['status' => 'partial_delivered']);
            }
        });

        session()->flash('success', 'Remisión registrada y stock actualizado.');
        $this->redirect(route('sales.orders.show', $this->order), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.delivery-form', [
            'warehouses' => Warehouse::where('is_active', true)->with('branch')->orderBy('name')->get(),
        ]);
    }
}