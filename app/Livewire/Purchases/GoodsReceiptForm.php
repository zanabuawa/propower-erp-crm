<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class GoodsReceiptForm extends Component
{
    public ?int    $purchase_order_id = null;
    public ?int    $supplier_id       = null;
    public ?int    $warehouse_id      = null;
    public string  $reception_type    = 'purchase';
    public string  $notes             = '';
    public string  $reference         = '';
    public float   $operating_expenses = 0;
    public array   $items             = [];

    // Búsqueda de productos
    public string  $productSearch     = '';
    public array   $productResults    = [];

    // Modal de confirmación
    public bool    $showConfirmModal  = false;

    public function mount(): void
    {
        $this->items = [];
        $this->setDefaultWarehouse();
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
    }

    public function updatedReceptionType(): void
    {
        $this->setDefaultWarehouse();
    }

    public function updatedPurchaseOrderId(): void
    {
        if (!$this->purchase_order_id) {
            $this->items = [];
            $this->supplier_id = null;
            return;
        }

        $order = PurchaseOrder::with(['items.product'])
            ->where('company_id', auth()->user()->company_id)
            ->find($this->purchase_order_id);

        if (!$order) return;

        $this->supplier_id = $order->supplier_id;
        $this->reference   = $order->folio;

        $existingIds = collect($this->items)->pluck('product_id')->toArray();

        foreach ($order->items as $orderItem) {
            $pending = (float) $orderItem->pending_quantity;
            if ($pending <= 0) continue;
            if (in_array($orderItem->product_id, $existingIds)) continue;

            $product = $orderItem->product;

            $this->items[] = [
                'order_item_id'    => $orderItem->id,
                'product_id'       => $orderItem->product_id,
                'product_name'     => $orderItem->description,
                'sku'              => $product?->sku ?? '',
                'quantity'         => $pending,
                'purchase_price'   => (float) $orderItem->unit_price,
                'profit_margin'    => (float) ($product?->profit_margin ?? 0),
                'operational_cost' => (float) ($product?->operational_costs ?? 0),
                'received'         => true,
                'notes'            => '',
            ];
            $existingIds[] = $orderItem->product_id;
        }
    }

    public function updatedProductSearch(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(fn($q) => $q
                ->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->orWhere('barcode', 'like', "%{$this->productSearch}%"))
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'purchase_price', 'profit_margin', 'operational_costs'])
            ->toArray();
    }

    #[On('product-picked')]
    public function productPicked(int $productId): void
    {
        $this->addProduct($productId);
    }

    public function addProduct(int $productId): void
    {
        foreach ($this->items as $item) {
            if ($item['product_id'] === $productId) {
                $this->productSearch  = '';
                $this->productResults = [];
                return;
            }
        }

        $product = Product::find($productId);
        if (!$product) return;

        $this->items[] = [
            'order_item_id'    => null,
            'product_id'       => $product->id,
            'product_name'     => $product->name,
            'sku'              => $product->sku ?? '',
            'quantity'         => 1,
            'purchase_price'   => (float) $product->purchase_price,
            'profit_margin'    => (float) $product->profit_margin,
            'operational_cost' => (float) $product->operational_costs,
            'received'         => true,
            'notes'            => '',
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
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

    private function validationRules(): array
    {
        $rules = [
            'warehouse_id'       => 'required|exists:warehouses,id',
            'reception_type'     => 'required|in:purchase,return,transfer,defective',
            'operating_expenses' => 'required|numeric|min:0',
            'items'              => 'required|array|min:1',
        ];

        foreach ($this->items as $index => $item) {
            if (!($item['received'] ?? true)) continue;

            $rules["items.{$index}.product_id"]       = 'required|exists:products,id';
            $rules["items.{$index}.quantity"]         = 'required|numeric|min:0.01';
            $rules["items.{$index}.purchase_price"]   = 'required|numeric|min:0';
            $rules["items.{$index}.profit_margin"]    = 'required|numeric|min:0|max:999';
            $rules["items.{$index}.operational_cost"] = 'required|numeric|min:0|max:999';
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate($this->validationRules());

        // Only save items marked as received
        $receivedItems = array_filter($this->items, fn($i) => $i['received'] ?? true);

        if (empty($receivedItems)) {
            $this->addError('items', 'Debes marcar al menos un producto como recibido.');
            return;
        }

        DB::transaction(function () use ($receivedItems) {
            $companyId = auth()->user()->company_id;

            $folio = 'REC-' . str_pad(
                PurchaseReceipt::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $movementType = match ($this->reception_type) {
                'return'   => 'return',
                'transfer' => 'transfer',
                default    => 'entry',
            };

            $movement = StockMovement::create([
                'company_id'   => $companyId,
                'warehouse_id' => $this->warehouse_id,
                'user_id'      => auth()->id(),
                'type'         => $movementType,
                'folio'        => $folio,
                'status'       => 'confirmed',
                'reference'    => $this->reference ?: ($this->purchase_order_id ? PurchaseOrder::find($this->purchase_order_id)?->folio : null),
                'notes'        => $this->notes ?: PurchaseReceipt::RECEPTION_TYPES[$this->reception_type],
                'moved_at'     => now(),
            ]);

            $receipt = PurchaseReceipt::create([
                'company_id'         => $companyId,
                'purchase_order_id'  => $this->purchase_order_id ?: null,
                'received_by'        => auth()->id(),
                'warehouse_id'       => $this->warehouse_id,
                'folio'              => $folio,
                'status'             => 'completed',
                'reception_type'     => $this->reception_type,
                'operating_expenses' => $this->operating_expenses,
                'notes'              => $this->notes ?: null,
                'received_at'        => now(),
            ]);

            foreach ($receivedItems as $item) {
                $productId        = $item['product_id'];
                $qty              = (float) $item['quantity'];
                $newPurchasePrice = (float) $item['purchase_price'];
                $profitMargin     = (float) $item['profit_margin'];
                $opCostPct        = (float) $item['operational_cost'];

                $stock = Stock::firstOrCreate(
                    ['product_id' => $productId, 'warehouse_id' => $this->warehouse_id],
                    ['quantity' => 0]
                );
                $qtyBefore = (float) $stock->quantity;
                $stock->increment('quantity', $qty);

                if ($this->reception_type !== 'defective') {
                    $newSalePrice  = round($newPurchasePrice * (1 + $profitMargin / 100), 2);
                    $productUpdate = [
                        'purchase_price'    => $newPurchasePrice,
                        'operational_costs' => $opCostPct,
                        'sale_price'        => $newSalePrice,
                    ];
                    if ($this->supplier_id) {
                        $productUpdate['supplier_id'] = $this->supplier_id;
                    }
                    Product::where('id', $productId)->update($productUpdate);
                }

                $movement->items()->create([
                    'product_id'      => $productId,
                    'warehouse_id'    => $this->warehouse_id,
                    'quantity'        => $qty,
                    'unit_price'      => $newPurchasePrice,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyBefore + $qty,
                ]);

                $receipt->items()->create([
                    'purchase_order_item_id' => $item['order_item_id'] ?? null,
                    'product_id'             => $productId,
                    'warehouse_id'           => $this->warehouse_id,
                    'quantity_received'      => $qty,
                    'notes'                  => $item['notes'] ?? null,
                ]);

                // Update PO item quantity_received if linked
                if ($item['order_item_id'] ?? null) {
                    PurchaseOrderItem::where('id', $item['order_item_id'])
                        ->increment('quantity_received', $qty);
                }
            }

            // Update PO status if linked
            if ($this->purchase_order_id) {
                $order = PurchaseOrder::with('items')->find($this->purchase_order_id);
                if ($order) {
                    $allReceived = $order->items->every(fn($i) => (float) $i->quantity_received >= (float) $i->quantity);
                    $anyReceived = $order->items->some(fn($i) => (float) $i->quantity_received > 0);

                    if ($allReceived) {
                        $order->update(['status' => 'received']);
                    } elseif ($anyReceived) {
                        $order->update(['status' => 'partial_received']);
                    }
                }
            }
        });

        $this->showConfirmModal = false;
        session()->flash('success', 'Recepción de mercancías registrada correctamente.');
        $this->redirect(route('purchases.goods-receipts.index'), navigate: true);
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

        $purchaseOrders = PurchaseOrder::where('company_id', $user->company_id)
            ->whereIn('status', ['sent', 'waiting_delivery', 'partial_received'])
            ->with('supplier')
            ->orderByDesc('created_at')
            ->get(['id', 'folio', 'supplier_id', 'status']);

        return view('livewire.purchases.goods-receipt-form', [
            'warehouses'     => $warehouses,
            'suppliers'      => Supplier::where('company_id', $user->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
            'purchaseOrders' => $purchaseOrders,
        ]);
    }
}
