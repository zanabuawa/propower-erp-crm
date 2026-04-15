<?php

namespace App\Livewire\Purchases;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ReceiptForm extends Component
{
    public PurchaseOrder $order;
    public ?int    $warehouse_id       = null;
    public string  $reception_type     = 'purchase';
    public float   $operating_expenses = 0;
    public string  $notes              = '';
    public array   $items              = [];

    // Finanzas
    public ?int   $financeAccountId = null;
    public array  $financeAccounts  = [];

    // Modal de confirmación
    public bool    $showConfirmModal   = false;

    public function mount($order): void
    {
        $this->order = $order instanceof PurchaseOrder
            ? $order
            : PurchaseOrder::with(['items.product'])->findOrFail($order);

        $this->setDefaultWarehouse();

        $this->items = $this->order->items
            ->filter(fn($i) => $i->pending_quantity > 0)
            ->map(fn($i) => [
                'order_item_id'     => $i->id,
                'product_id'        => $i->product_id,
                'product_name'      => $i->description,
                'purchase_price'    => $i->unit_price,
                'warehouse_id'      => $this->warehouse_id,
                'quantity_ordered'  => $i->quantity,
                'quantity_received' => $i->pending_quantity,
                'quantity_pending'  => $i->pending_quantity,
                'operational_cost'  => '0',
                'notes'             => '',
                'received'          => true,
            ])->values()->toArray();

        $this->financeAccounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'currency', 'current_balance'])
            ->toArray();
    }

    private function setDefaultWarehouse(): void
    {
        $user = auth()->user();

        if ($this->reception_type === 'defective') {
            $defective = Warehouse::where('company_id', $user->company_id)
                ->where('is_defective', true)
                ->where('is_active', true)
                ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->first();
            $this->warehouse_id = $defective?->id;
        } else {
            $this->warehouse_id = $user->branch_id
                ? Warehouse::where('branch_id', $user->branch_id)
                    ->where('is_active', true)
                    ->where('is_defective', false)
                    ->first()?->id
                : null;
        }

        // Propagar a todos los ítems
        foreach ($this->items as $index => $_) {
            $this->items[$index]['warehouse_id'] = $this->warehouse_id;
        }
    }

    public function updatedReceptionType(): void
    {
        $this->setDefaultWarehouse();
    }

    public function updatedWarehouseId(): void
    {
        foreach ($this->items as $index => $_) {
            $this->items[$index]['warehouse_id'] = $this->warehouse_id;
        }
    }

    private function validationRules(): array
    {
        $rules = [
            'warehouse_id'               => 'required|exists:warehouses,id',
            'reception_type'             => 'required|in:purchase,return,transfer,defective',
            'operating_expenses'         => 'required|numeric|min:0',
            'items'                      => 'required|array|min:1',
            'items.*.quantity_received'  => 'required|numeric|min:0',
            'items.*.warehouse_id'       => 'required|exists:warehouses,id',
            'items.*.operational_cost'   => 'required|numeric|min:0',
        ];

        if ($this->reception_type === 'purchase') {
            $rules['financeAccountId'] = 'required|exists:finance_accounts,id';
        }

        return $rules;
    }

    public function confirm(): void
    {
        $this->validate($this->validationRules());
        $this->showConfirmModal = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirmModal = false;
    }

    public function save(): void
    {
        $this->validate($this->validationRules());

        DB::transaction(function () {
            $folio = 'REC-' . str_pad(
                PurchaseReceipt::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $receipt = PurchaseReceipt::create([
                'company_id'         => auth()->user()->company_id,
                'purchase_order_id'  => $this->order->id,
                'received_by'        => auth()->id(),
                'warehouse_id'       => $this->warehouse_id,
                'folio'              => $folio,
                'status'             => 'completed',
                'reception_type'     => $this->reception_type,
                'operating_expenses' => $this->operating_expenses,
                'notes'              => $this->notes,
                'received_at'        => now(),
            ]);

            $movementType = match ($this->reception_type) {
                'return'    => 'return',
                'transfer'  => 'transfer',
                default     => 'entry',
            };

            // Registrar egreso en finanzas para recepciones de compra
            if ($this->reception_type === 'purchase' && $this->financeAccountId) {
                $receivedItems  = collect($this->items)->filter(fn($i) => ($i['received'] ?? true) && $i['quantity_received'] > 0);
                $totalMercancia = $receivedItems->sum(fn($i) => (float) $i['quantity_received'] * (float) $i['purchase_price']);
                $totalEgreso    = $totalMercancia + (float) $this->operating_expenses;

                FinanceTransaction::create([
                    'account_id'       => $this->financeAccountId,
                    'registered_by'    => auth()->id(),
                    'folio'            => 'TXN-' . $receipt->folio,
                    'type'             => 'egreso',
                    'concept'          => 'Compra: ' . $receipt->folio . ' — OC ' . $this->order->folio,
                    'category'         => 'compra',
                    'amount'           => $totalEgreso,
                    'currency'         => $this->order->currency ?? 'MXN',
                    'exchange_rate'    => 1,
                    'transaction_date' => now()->toDateString(),
                    'reference'        => $receipt->folio,
                    'status'           => 'confirmado',
                    'notes'            => $this->notes ?: null,
                ]);
            }

            foreach ($this->items as $item) {
                if (!($item['received'] ?? true)) continue;
                if ($item['quantity_received'] <= 0) continue;

                $warehouseId = $this->reception_type === 'defective'
                    ? $this->warehouse_id   // todos van al almacén defectuoso
                    : $item['warehouse_id'];

                $receipt->items()->create([
                    'purchase_order_item_id' => $item['order_item_id'],
                    'product_id'             => $item['product_id'],
                    'warehouse_id'           => $warehouseId,
                    'quantity_received'      => $item['quantity_received'],
                    'notes'                  => $item['notes'] ?? null,
                ]);

                $orderItem = PurchaseOrderItem::find($item['order_item_id']);
                $orderItem->increment('quantity_received', $item['quantity_received']);

                if ($item['product_id']) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $warehouseId],
                        ['quantity' => 0]
                    );
                    $quantityBefore = $stock->quantity;
                    $stock->increment('quantity', $item['quantity_received']);

                    // No actualizar precios si es defectuoso
                    if ($this->reception_type !== 'defective') {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            $newPurchasePrice = (float) $item['purchase_price'];
                            $newOpCostPct     = (float) ($item['operational_cost'] ?? 0);
                            $newSalePrice     = round($newPurchasePrice * (1 + $product->profit_margin / 100), 2);

                            $product->update([
                                'purchase_price'    => $newPurchasePrice,
                                'operational_costs' => $newOpCostPct,
                                'sale_price'        => $newSalePrice,
                            ]);
                        }
                    }

                    $movement = StockMovement::create([
                        'company_id'   => auth()->user()->company_id,
                        'warehouse_id' => $warehouseId,
                        'user_id'      => auth()->id(),
                        'type'         => $movementType,
                        'folio'        => $folio,
                        'status'       => 'confirmed',
                        'reference'    => $this->order->folio,
                        'notes'        => PurchaseReceipt::RECEPTION_TYPES[$this->reception_type] . ' — ' . $this->order->folio,
                        'moved_at'     => now(),
                    ]);

                    $movement->items()->create([
                        'product_id'      => $item['product_id'],
                        'warehouse_id'    => $warehouseId,
                        'quantity'        => $item['quantity_received'],
                        'unit_price'      => $item['purchase_price'] ?? 0,
                        'quantity_before' => $quantityBefore,
                        'quantity_after'  => $stock->fresh()->quantity,
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

        $this->showConfirmModal = false;
        session()->flash('success', 'Recepción registrada y stock actualizado.');
        $this->redirect(route('purchases.orders.show', $this->order), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        if ($this->reception_type === 'defective') {
            $warehouses = Warehouse::where('company_id', $user->company_id)
                ->where('is_active', true)
                ->where('is_defective', true)
                ->with('branch')
                ->orderBy('name')
                ->get();
        } else {
            $warehouses = Warehouse::forUser($user)
                ->where('is_defective', false)
                ->with('branch')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.purchases.receipt-form', [
            'warehouses' => $warehouses,
        ]);
    }
}
