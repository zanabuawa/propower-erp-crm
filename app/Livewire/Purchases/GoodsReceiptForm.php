<?php

namespace App\Livewire\Purchases;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\SaleDelivery;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Warehouse;
use App\Notifications\PurchaseNotification;
use App\Services\FifoStockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class GoodsReceiptForm extends Component
{
    // Documento origen según tipo de recepción
    public ?int    $purchase_order_id  = null;   // purchase
    public ?int    $sale_delivery_id   = null;   // return
    public ?int    $origin_movement_id = null;   // transfer

    // Datos generales
    public ?int    $warehouse_id        = null;
    public string  $reception_type      = 'purchase';
    public string  $notes               = '';
    public string  $reference           = '';
    public float   $operating_expenses  = 0;
    public array   $items               = [];

    // Almacén origen (solo display, transferencias)
    public ?int    $origin_warehouse_id  = null;
    public string  $originWarehouseName  = '';

    // Búsqueda de productos
    public string  $productSearch     = '';
    public array   $productResults    = [];

    // Finanzas
    public ?int   $financeAccountId = null;
    public array  $financeAccounts  = [];

    // Modal de confirmación
    public bool    $showConfirmModal  = false;

    // Alertas de varianza de precio (producto => [prev, new, pct])
    public array   $priceWarnings     = [];

    public function mount(): void
    {
        $this->items = [];
        $this->setDefaultWarehouse();

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
    }

    // ── Actualizadores de tipo y documentos origen ───────────────────────────

    public function updatedReceptionType(): void
    {
        // Limpiar documento origen, ítems y referencia al cambiar tipo
        $this->purchase_order_id   = null;
        $this->sale_delivery_id    = null;
        $this->origin_movement_id  = null;
        $this->origin_warehouse_id = null;
        $this->originWarehouseName = '';
        $this->items               = [];
        $this->reference          = '';
        $this->setDefaultWarehouse();
    }

    /**
     * Carga ítems pendientes de una OC seleccionada.
     */
    public function updatedPurchaseOrderId(): void
    {
        if (!$this->purchase_order_id) {
            $this->items       = [];
            $this->supplier_id = null;
            $this->reference   = '';
            return;
        }

        $order = PurchaseOrder::with(['items.product'])
            ->where('company_id', auth()->user()->company_id)
            ->find($this->purchase_order_id);

        if (!$order) return;

        $this->reference = $order->folio;

        $existingIds = collect($this->items)->pluck('product_id')->toArray();
        $loaded      = 0;

        foreach ($order->items as $orderItem) {
            // Usar precisión de 4 decimales para evitar errores de punto flotante
            $pending = round((float) $orderItem->quantity - (float) $orderItem->quantity_received, 4);
            if ($pending <= 0.0001) continue;
            if (in_array($orderItem->product_id, $existingIds)) continue;

            $product = $orderItem->product;

            $this->items[] = [
                'order_item_id'      => $orderItem->id,
                'movement_item_id'   => null,
                'product_id'         => $orderItem->product_id,
                'product_name'       => $orderItem->description,
                'sku'                => $product?->sku ?? '',
                'quantity'           => $pending,
                'quantity_ordered'   => (float) $orderItem->quantity,
                'purchase_price'     => (float) $orderItem->unit_price,
                'prev_purchase_price'=> (float) ($product?->purchase_price ?? $orderItem->unit_price),
                'profit_margin'      => (float) ($product?->profit_margin ?? 0),
                'operational_cost'   => (float) ($product?->operational_costs ?? 0),
                'received'           => true,
                'notes'              => '',
                'quantity_rejected'  => 0,
                'rejection_reason'   => '',
            ];
            $existingIds[] = $orderItem->product_id;
            $loaded++;
        }

        if ($loaded === 0) {
            $this->addError('purchase_order_id', 'Todos los productos de esta orden ya fueron recibidos completamente.');
        }
    }

    /**
     * Carga ítems de una entrega de venta para procesar devolución.
     */
    public function updatedSaleDeliveryId(): void
    {
        if (!$this->sale_delivery_id) {
            $this->items     = [];
            $this->reference = '';
            return;
        }

        $delivery = SaleDelivery::with(['items.product'])
            ->where('company_id', auth()->user()->company_id)
            ->where('status', 'delivered')
            ->find($this->sale_delivery_id);

        if (!$delivery) return;

        $this->reference    = $delivery->folio;
        $this->warehouse_id = $delivery->warehouse_id;

        $existingIds = collect($this->items)->pluck('product_id')->toArray();

        foreach ($delivery->items as $deliveryItem) {
            $qty = round((float) $deliveryItem->quantity, 4);
            if ($qty <= 0) continue;
            if (in_array($deliveryItem->product_id, $existingIds)) continue;

            $product = $deliveryItem->product;

            $this->items[] = [
                'order_item_id'    => null,
                'movement_item_id' => null,
                'product_id'       => $deliveryItem->product_id,
                'product_name'     => $product?->name ?? 'Producto eliminado',
                'sku'              => $product?->sku ?? '',
                'quantity'         => $qty,
                'purchase_price'   => (float) ($product?->purchase_price ?? 0),
                'profit_margin'    => (float) ($product?->profit_margin ?? 0),
                'operational_cost' => (float) ($product?->operational_costs ?? 0),
                'received'         => true,
                'notes'            => '',
            ];
            $existingIds[] = $deliveryItem->product_id;
        }
    }

    /**
     * Carga ítems pendientes de una transferencia en tránsito.
     */
    public function updatedOriginMovementId(): void
    {
        if (!$this->origin_movement_id) {
            $this->items               = [];
            $this->reference           = '';
            $this->origin_warehouse_id = null;
            $this->originWarehouseName = '';
            return;
        }

        $movement = StockMovement::with(['items.product', 'warehouse', 'warehouseDestination'])
            ->where('company_id', auth()->user()->company_id)
            ->where('type', 'transfer')
            ->whereIn('status', ['in_transit', 'partially_received'])
            ->find($this->origin_movement_id);

        if (!$movement) return;

        // El destino de la transferencia es el almacén que recibe
        $this->warehouse_id        = $movement->warehouse_destination_id;
        $this->origin_warehouse_id = $movement->warehouse_id;
        $this->originWarehouseName = $movement->warehouse?->name ?? '';
        $this->reference           = $movement->folio;

        $existingIds = collect($this->items)->pluck('product_id')->toArray();

        foreach ($movement->items as $movItem) {
            $pending = round((float) $movItem->quantity - (float) ($movItem->received_quantity ?? 0), 4);
            if ($pending <= 0.0001) continue;
            if (in_array($movItem->product_id, $existingIds)) continue;

            $product = $movItem->product;

            $this->items[] = [
                'order_item_id'    => null,
                'movement_item_id' => $movItem->id,
                'product_id'       => $movItem->product_id,
                'product_name'     => $product?->name ?? 'Producto eliminado',
                'sku'              => $product?->sku ?? '',
                'quantity'         => $pending,
                'purchase_price'   => (float) ($product?->purchase_price ?? 0),
                'profit_margin'    => (float) ($product?->profit_margin ?? 0),
                'operational_cost' => (float) ($product?->operational_costs ?? 0),
                'received'         => true,
                'notes'            => '',
            ];
            $existingIds[] = $movItem->product_id;
        }
    }

    // ── Búsqueda manual de productos ─────────────────────────────────────────

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
            'order_item_id'       => null,
            'movement_item_id'    => null,
            'product_id'          => $product->id,
            'product_name'        => $product->name,
            'sku'                 => $product->sku ?? '',
            'quantity'            => 1,
            'quantity_ordered'    => 0,
            'purchase_price'      => (float) $product->purchase_price,
            'prev_purchase_price' => (float) $product->purchase_price,
            'profit_margin'       => (float) $product->profit_margin,
            'operational_cost'    => (float) $product->operational_costs,
            'received'            => true,
            'notes'               => '',
            'quantity_rejected'   => 0,
            'rejection_reason'    => '',
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    // ── Confirmación y guardado ──────────────────────────────────────────────

    public function confirm(): void
    {
        $this->validate($this->validationRules());
        $this->priceWarnings = $this->detectPriceWarnings();
        $this->showConfirmModal = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirmModal = false;
    }

    private function detectPriceWarnings(): array
    {
        $warnings = [];
        foreach ($this->items as $item) {
            if (!($item['received'] ?? true)) continue;
            $prev = (float) ($item['prev_purchase_price'] ?? 0);
            $new  = (float) ($item['purchase_price'] ?? 0);
            if ($prev <= 0 || $new <= 0) continue;
            $pct = round(abs($new - $prev) / $prev * 100, 1);
            if ($pct >= 3) { // advertir si cambia ≥3%
                $warnings[] = [
                    'name'     => $item['product_name'],
                    'prev'     => $prev,
                    'new'      => $new,
                    'pct'      => $pct,
                    'increase' => $new > $prev,
                ];
            }
        }
        return $warnings;
    }

    private function validationRules(): array
    {
        $rules = [
            'warehouse_id'       => 'required|exists:warehouses,id',
            'reception_type'     => 'required|in:purchase,return,transfer,defective',
            'operating_expenses' => 'required|numeric|min:0',
            'items'              => 'required|array|min:1',
        ];

        if ($this->reception_type === 'purchase') {
            $rules['financeAccountId'] = 'required|exists:finance_accounts,id';
        }

        if ($this->reception_type === 'return') {
            $rules['sale_delivery_id'] = 'nullable|exists:sale_deliveries,id';
        }

        if ($this->reception_type === 'transfer') {
            $rules['origin_movement_id'] = 'nullable|exists:stock_movements,id';
        }

        foreach ($this->items as $index => $item) {
            if (!($item['received'] ?? true)) continue;

            $rules["items.{$index}.product_id"]        = 'required|exists:products,id';
            $rules["items.{$index}.quantity"]           = 'required|numeric|min:0.01';
            $rules["items.{$index}.purchase_price"]     = 'required|numeric|min:0';
            $rules["items.{$index}.profit_margin"]      = 'required|numeric|min:0|max:999';
            $rules["items.{$index}.operational_cost"]   = 'required|numeric|min:0|max:999';
            $rules["items.{$index}.quantity_rejected"]  = 'nullable|numeric|min:0';
            $rules["items.{$index}.rejection_reason"]   = 'nullable|string';
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate($this->validationRules());

        $receivedItems = array_filter($this->items, fn($i) => $i['received'] ?? true);

        if (empty($receivedItems)) {
            $this->addError('items', 'Debes marcar al menos un producto como recibido.');
            return;
        }

        DB::transaction(function () use ($receivedItems) {
            $companyId = auth()->user()->company_id;
            $fifo      = app(FifoStockService::class);

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
                'reference'    => $this->reference ?: null,
                'notes'        => $this->notes ?: PurchaseReceipt::RECEPTION_TYPES[$this->reception_type],
                'moved_at'     => now(),
            ]);

            $receipt = PurchaseReceipt::create([
                'company_id'         => $companyId,
                'purchase_order_id'  => $this->purchase_order_id  ?: null,
                'sale_delivery_id'   => $this->sale_delivery_id   ?: null,
                'origin_movement_id' => $this->origin_movement_id ?: null,
                'received_by'        => auth()->id(),
                'warehouse_id'       => $this->warehouse_id,
                'folio'              => $folio,
                'status'             => 'completed',
                'reception_type'     => $this->reception_type,
                'operating_expenses' => $this->operating_expenses,
                'notes'              => $this->notes ?: null,
                'received_at'        => now(),
            ]);

            // Registrar egreso en finanzas para recepciones de compra
            if ($this->reception_type === 'purchase' && $this->financeAccountId) {
                $totalMercancia = collect($receivedItems)->sum(
                    fn($i) => (float) $i['quantity'] * (float) $i['purchase_price']
                );
                $totalEgreso = $totalMercancia + (float) $this->operating_expenses;

                FinanceTransaction::create([
                    'account_id'       => $this->financeAccountId,
                    'registered_by'    => auth()->id(),
                    'folio'            => 'TXN-' . $receipt->folio,
                    'type'             => 'egreso',
                    'concept'          => 'Compra: ' . $receipt->folio .
                        ($this->purchase_order_id ? ' — OC ' . optional(PurchaseOrder::find($this->purchase_order_id))->folio : ''),
                    'category'         => 'compra',
                    'amount'           => $totalEgreso,
                    'currency'         => 'MXN',
                    'exchange_rate'    => 1,
                    'transaction_date' => now()->toDateString(),
                    'reference'        => $receipt->folio,
                    'status'           => 'confirmado',
                    'notes'            => $this->notes ?: null,
                ]);
            }

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

                // Crear lote PEPS para compras, devoluciones y transferencias
                if (in_array($this->reception_type, ['purchase', 'return', 'transfer'])) {
                    $lotMovementType = match ($this->reception_type) {
                        'return'   => 'return_sale',
                        'transfer' => 'transfer_in',
                        default    => 'purchase',
                    };
                    $fifo->createLot(
                        companyId:    $companyId,
                        productId:    $productId,
                        warehouseId:  $this->warehouse_id,
                        quantity:     $qty,
                        unitCost:     $newPurchasePrice,
                        reference:    $receipt->folio,
                        expiryDate:   null,
                        notes:        "Recepción {$receipt->folio}",
                        movementType: $lotMovementType,
                        movedAt:      now(),
                    );
                }

                // Actualizar precios del producto (no aplica para defectuosos ni transferencias)
                if (!in_array($this->reception_type, ['defective', 'transfer'])) {
                    $marginDiv     = 1 - $profitMargin / 100;
                    $newSalePrice  = $marginDiv > 0 ? round($newPurchasePrice / $marginDiv, 2) : 0;
                    Product::where('id', $productId)->update([
                        'purchase_price'    => $newPurchasePrice,
                        'profit_margin'     => $profitMargin,
                        'operational_costs' => $opCostPct,
                        'sale_price'        => $newSalePrice,
                    ]);
                }

                $movement->items()->create([
                    'product_id'      => $productId,
                    'warehouse_id'    => $this->warehouse_id,
                    'quantity'        => $qty,
                    'unit_price'      => $newPurchasePrice,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyBefore + $qty,
                ]);

                $qtyRejected      = (float) ($item['quantity_rejected'] ?? 0);
                $rejectionReason  = $item['rejection_reason'] ?? null;

                $receipt->items()->create([
                    'purchase_order_item_id' => $item['order_item_id'] ?? null,
                    'product_id'             => $productId,
                    'warehouse_id'           => $this->warehouse_id,
                    'quantity_received'      => $qty,
                    'notes'                  => $item['notes'] ?? null,
                    'quantity_rejected'      => $qtyRejected,
                    'rejection_reason'       => $qtyRejected > 0 ? ($rejectionReason ?: 'other') : null,
                    'rejected_by'            => $qtyRejected > 0 ? auth()->id() : null,
                    'rejected_at'            => $qtyRejected > 0 ? now() : null,
                ]);

                // Actualizar cantidad recibida en ítem de OC
                if ($item['order_item_id'] ?? null) {
                    PurchaseOrderItem::where('id', $item['order_item_id'])
                        ->increment('quantity_received', $qty);
                }
            }

            // Actualizar estado de la OC vinculada
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

            // Actualizar cantidad recibida en ítems de la transferencia vinculada
            if ($this->reception_type === 'transfer' && $this->origin_movement_id) {
                $originMovement = StockMovement::with('items')->find($this->origin_movement_id);

                if ($originMovement) {
                    foreach ($receivedItems as $item) {
                        $movItemId = $item['movement_item_id'] ?? null;
                        if (!$movItemId) continue;

                        $movItem = StockMovementItem::find($movItemId);
                        if (!$movItem) continue;

                        $movItem->update([
                            'received_quantity' => (float) ($movItem->received_quantity ?? 0) + (float) $item['quantity'],
                            'received_at'       => $movItem->received_at ?? now(),
                        ]);
                    }

                    // Recalcular estado de la transferencia
                    $movItems      = $originMovement->items()->get();
                    $receivedCount = $movItems->filter(
                        fn($i) => round((float) ($i->received_quantity ?? 0), 4) >= round((float) $i->quantity - 0.0001, 4)
                    )->count();

                    $newStatus = match (true) {
                        $receivedCount === 0                  => 'in_transit',
                        $receivedCount < $movItems->count()   => 'partially_received',
                        default                               => 'completed',
                    };

                    $originMovement->update(['status' => $newStatus]);
                }
            }
        });

        // Notificar al creador de la OC si hay faltantes o rechazos
        if ($this->purchase_order_id) {
            $order = PurchaseOrder::with('createdBy')->find($this->purchase_order_id);
            if ($order && $order->createdBy) {
                $shortfalls = collect($receivedItems)->filter(function ($item) {
                    $ordered   = (float) ($item['quantity_ordered'] ?? 0);
                    $received  = (float) ($item['quantity'] ?? 0);
                    $rejected  = (float) ($item['quantity_rejected'] ?? 0);
                    return $ordered > 0 && ($received < $ordered || $rejected > 0);
                });

                if ($shortfalls->count() > 0) {
                    $lines = $shortfalls->map(fn($i) =>
                        "- {$i['product_name']}: pedido {$i['quantity_ordered']}, recibido {$i['quantity']}"
                        . ((float)$i['quantity_rejected'] > 0 ? ", rechazado {$i['quantity_rejected']}" : '')
                    )->implode("\n");

                    $order->createdBy->notify(new PurchaseNotification(
                        title: 'Faltantes o rechazos en recepción',
                        message: "La recepción contiene faltantes o rechazos:\n{$lines}",
                        type: 'receipt_shortfall',
                        orderId: $order->id,
                    ));
                }
            }
        }

        $this->showConfirmModal = false;
        session()->flash('success', 'Recepción de mercancías registrada correctamente.');
        $this->redirect(route('purchases.goods-receipts.index'), navigate: true);
    }

    // ── Render ───────────────────────────────────────────────────────────────

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

        // OCs: mostrar todas las activas que no estén completamente recibidas
        $purchaseOrders = ($this->reception_type === 'purchase')
            ? PurchaseOrder::where('company_id', $user->company_id)
                ->whereNotIn('status', ['received', 'invoiced', 'cancelled'])
                ->with('supplier')
                ->orderByDesc('created_at')
                ->get(['id', 'folio', 'supplier_id', 'status'])
            : collect();

        // Entregas de venta completadas (para devoluciones)
        $saleDeliveries = ($this->reception_type === 'return')
            ? SaleDelivery::where('company_id', $user->company_id)
                ->where('status', 'delivered')
                ->with('customer')
                ->orderByDesc('delivered_at')
                ->get(['id', 'folio', 'customer_id', 'delivered_at', 'warehouse_id'])
            : collect();

        // Transferencias en tránsito (para recepciones de transferencia)
        $pendingTransfers = ($this->reception_type === 'transfer')
            ? StockMovement::where('company_id', $user->company_id)
                ->where('type', 'transfer')
                ->whereIn('status', ['in_transit', 'partially_received'])
                ->with(['warehouse', 'warehouseDestination'])
                ->orderByDesc('created_at')
                ->get(['id', 'folio', 'warehouse_id', 'warehouse_destination_id', 'status'])
            : collect();

        return view('livewire.purchases.goods-receipt-form', [
            'warehouses'       => $warehouses,
            'purchaseOrders'   => $purchaseOrders,
            'saleDeliveries'   => $saleDeliveries,
            'pendingTransfers' => $pendingTransfers,
        ]);
    }
}
