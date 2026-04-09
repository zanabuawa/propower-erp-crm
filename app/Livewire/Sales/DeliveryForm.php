<?php

namespace App\Livewire\Sales;

use App\Models\SaleDelivery;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\FifoStockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DeliveryForm extends Component
{
    public ?SaleOrder $order = null;

    // ── Cabecera ─────────────────────────────────────────────────────────────
    public ?int    $warehouse_id = null;
    public string  $reason       = 'sale_order';
    public string  $notes        = '';

    // ── Modo: completa / parcial ─────────────────────────────────────────────
    public string $delivery_mode = 'full'; // full | partial

    // ── Ítems con asignaciones de lote ───────────────────────────────────────
    // Estructura de cada ítem:
    // [
    //   'order_item_id'        => int,
    //   'product_id'           => int,
    //   'product_name'         => string,
    //   'quantity_pending'     => float,
    //   'quantity_to_deliver'  => float,   ← editable
    //   'warehouse_id'         => int,
    //   'lot_lines'            => [        ← FIFO sugerido, editable
    //     ['lot_id', 'lot_number', 'barcode', 'entry_date', 'available', 'quantity', 'unit_cost']
    //   ],
    //   'lot_available'        => float,   ← total disponible en lotes
    //   'include'              => bool,    ← si se incluye en esta entrega
    // ]
    public array $items = [];

    // ── Búsqueda de escaneo (opcional) ────────────────────────────────────────
    public string $scanInput = '';

    public function mount($order = null): void
    {
        if ($order) {
            $this->order = $order instanceof SaleOrder
                ? $order
                : SaleOrder::with(['items.product'])->findOrFail($order);
        }

        $defaultWarehouse = auth()->user()->branch_id
            ? Warehouse::where('branch_id', auth()->user()->branch_id)
                ->where('is_active', true)->first()?->id
            : null;

        $this->warehouse_id = $defaultWarehouse;

        if ($this->order) {
            $this->buildItemsFromOrder();
        }
    }

    // ── Rebuild items cuando cambia almacén ───────────────────────────────────

    public function updatedWarehouseId(): void
    {
        foreach ($this->items as $index => $item) {
            $this->items[$index]['warehouse_id'] = $this->warehouse_id;
        }
        $this->reloadAllLots();
    }

    // ── Rebuild lotes cuando cambia cantidad a entregar ───────────────────────

    public function updatedItems(mixed $value, string $key): void
    {
        // key es "0.quantity_to_deliver" o "0.warehouse_id"
        if (str_ends_with($key, '.quantity_to_deliver') || str_ends_with($key, '.warehouse_id')) {
            [$index] = explode('.', $key);
            $this->recalcLotsForItem((int) $index);
        }
    }

    // ── Modo entrega completa ─────────────────────────────────────────────────

    public function setFullDelivery(): void
    {
        $this->delivery_mode = 'full';
        foreach ($this->items as $index => $item) {
            $this->items[$index]['quantity_to_deliver'] = $item['quantity_pending'];
            $this->items[$index]['include']             = true;
        }
        $this->reloadAllLots();
    }

    // ── Modo entrega parcial (el usuario ajusta cantidades manualmente) ────────

    public function setPartialDelivery(): void
    {
        $this->delivery_mode = 'partial';
    }

    // ── Escaneo de lote por código de barras ──────────────────────────────────

    public function updatedScanInput(): void
    {
        if (strlen($this->scanInput) < 8) return;

        $barcode = trim($this->scanInput);

        // Buscar el lote escaneado en los ítems
        foreach ($this->items as $index => $item) {
            foreach ($item['lot_lines'] as $li => $line) {
                if ($line['barcode'] === $barcode) {
                    session()->flash('scan_ok', "Lote {$line['lot_number']} localizado en ítem: {$item['product_name']}");
                    $this->scanInput = '';
                    return;
                }
            }
        }

        session()->flash('scan_error', "Código «{$barcode}» no encontrado en los lotes de esta entrega.");
        $this->scanInput = '';
    }

    // ── Helpers internos ──────────────────────────────────────────────────────

    private function buildItemsFromOrder(): void
    {
        $fifo    = app(FifoStockService::class);
        $warehouseId = $this->warehouse_id;

        $this->items = $this->order->items
            ->filter(fn($i) => $i->pending_quantity > 0)
            ->values()
            ->map(function ($i) use ($fifo, $warehouseId) {
                $lotLines     = $warehouseId
                    ? $fifo->suggestAllocations($i->product_id, $warehouseId, (float) $i->pending_quantity)
                    : [];
                $lotAvailable = $warehouseId
                    ? $fifo->availableInLots($i->product_id, $warehouseId)
                    : 0;

                return [
                    'order_item_id'       => $i->id,
                    'product_id'          => $i->product_id,
                    'product_name'        => $i->description,
                    'quantity_pending'    => (float) $i->pending_quantity,
                    'quantity_to_deliver' => (float) $i->pending_quantity,
                    'unit_price'          => (float) $i->unit_price,  // precio de venta para kardex
                    'warehouse_id'        => $warehouseId,
                    'lot_lines'           => $lotLines,
                    'lot_available'       => $lotAvailable,
                    'include'             => true,
                ];
            })
            ->toArray();
    }

    private function reloadAllLots(): void
    {
        $fifo = app(FifoStockService::class);
        foreach ($this->items as $index => $item) {
            $wId = $item['warehouse_id'] ?? $this->warehouse_id;
            if (!$wId || !$item['product_id']) continue;

            $qty = (float) $item['quantity_to_deliver'];
            $this->items[$index]['lot_lines']    = $fifo->suggestAllocations($item['product_id'], $wId, $qty);
            $this->items[$index]['lot_available'] = $fifo->availableInLots($item['product_id'], $wId);
        }
    }

    private function recalcLotsForItem(int $index): void
    {
        $fifo = app(FifoStockService::class);
        $item = $this->items[$index];
        $wId  = $item['warehouse_id'] ?? $this->warehouse_id;
        if (!$wId || !$item['product_id']) return;

        $qty = (float) $item['quantity_to_deliver'];
        $this->items[$index]['lot_lines']    = $fifo->suggestAllocations($item['product_id'], $wId, $qty);
        $this->items[$index]['lot_available'] = $fifo->availableInLots($item['product_id'], $wId);
    }

    // ── Validación ────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'warehouse_id'                      => 'required|exists:warehouses,id',
            'reason'                            => 'required|in:sale_order,internal_use,scrap,return_to_supplier,other',
            'items'                             => 'required|array|min:1',
            'items.*.quantity_to_deliver'       => 'required|numeric|min:0',
            'items.*.warehouse_id'              => 'required|exists:warehouses,id',
            'items.*.lot_lines'                 => 'array',
            'items.*.lot_lines.*.lot_id'        => 'required|exists:product_lots,id',
            'items.*.lot_lines.*.quantity'      => 'required|numeric|min:0',
        ];
    }

    // ── Guardar ───────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        // Verificar que haya al menos un ítem con cantidad > 0
        $hasAny = collect($this->items)->some(
            fn($i) => $i['include'] && (float) $i['quantity_to_deliver'] > 0
        );

        if (!$hasAny) {
            $this->addError('items', 'Debe indicar al menos un producto con cantidad mayor a 0.');
            return;
        }

        DB::transaction(function () {
            $fifo  = app(FifoStockService::class);
            $companyId = auth()->user()->company_id;

            $folio = 'REM-' . str_pad(
                SaleDelivery::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $delivery = SaleDelivery::create([
                'company_id'    => $companyId,
                'sale_order_id' => $this->order?->id,
                'customer_id'   => $this->order?->customer_id,
                'created_by'    => auth()->id(),
                'warehouse_id'  => $this->warehouse_id,
                'folio'         => $folio,
                'status'        => 'delivered',
                'reason'        => $this->reason,
                'notes'         => $this->notes,
                'delivered_at'  => now(),
            ]);

            $movFolio = 'MOV-S-' . str_pad(
                StockMovement::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $movement = StockMovement::create([
                'company_id'   => $companyId,
                'warehouse_id' => $this->warehouse_id,
                'user_id'      => auth()->id(),
                'type'         => 'exit',
                'folio'        => $movFolio,
                'status'       => 'confirmed',
                'reference'    => $this->order?->folio ?? $folio,
                'notes'        => SaleDelivery::REASONS[$this->reason] ?? $this->reason,
                'moved_at'     => now(),
            ]);

            foreach ($this->items as $item) {
                if (!$item['include']) continue;
                $qty = (float) $item['quantity_to_deliver'];
                if ($qty <= 0) continue;

                $warehouseId = $item['warehouse_id'] ?? $this->warehouse_id;

                // Consumir lotes en orden PEPS confirmado por el usuario
                foreach ($item['lot_lines'] as $line) {
                    $lotQty = (float) $line['quantity'];
                    if ($lotQty <= 0) continue;

                    // Crear ítem de remisión con referencia al lote
                    $delivery->items()->create([
                        'sale_order_item_id' => $item['order_item_id'] ?? null,
                        'product_id'         => $item['product_id'],
                        'warehouse_id'       => $warehouseId,
                        'lot_id'             => $line['lot_id'],
                        'quantity'           => $lotQty,
                    ]);

                    // Descontar stock agregado
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $warehouseId],
                        ['quantity'   => 0]
                    );
                    $qtyBefore = (float) $stock->quantity;
                    $stock->decrement('quantity', $lotQty);

                    // Movimiento de inventario con lote
                    $movement->items()->create([
                        'product_id'      => $item['product_id'],
                        'warehouse_id'    => $warehouseId,
                        'lot_id'          => $line['lot_id'],
                        'quantity'        => $lotQty,
                        'unit_price'      => $line['unit_cost'] ?? 0,
                        'quantity_before' => $qtyBefore,
                        'quantity_after'  => $stock->fresh()->quantity,
                    ]);
                }

                // Consumir lotes (decrementar ProductLot.quantity) y registrar kardex PEPS
                $kardexType = match($this->reason) {
                    'sale_order'         => 'sale',
                    'return_to_supplier' => 'return_purchase',
                    'internal_use'       => 'internal_use',
                    'scrap'              => 'scrap',
                    default              => 'other',
                };

                $fifo->consumeLots(
                    allocations:   $item['lot_lines'],
                    unitSalePrice: (float) ($item['unit_price'] ?? 0),
                    movementType:  $kardexType,
                    reference:     $folio,
                    companyId:     $companyId,
                    warehouseId:   $warehouseId,
                    movedAt:       now(),
                    notes:         SaleDelivery::REASONS[$this->reason] ?? '',
                );

                // Actualizar cantidad entregada en ítem de orden
                if (!empty($item['order_item_id'])) {
                    SaleOrderItem::find($item['order_item_id'])
                        ?->increment('quantity_delivered', $qty);
                }
            }

            // Actualizar estado de la orden de venta si existe
            if ($this->order) {
                $order = $this->order->fresh()->load('items');
                $allDelivered = $order->items->every(fn($i) => $i->quantity_delivered >= $i->quantity);
                $anyDelivered = $order->items->some(fn($i)  => $i->quantity_delivered > 0);

                if ($allDelivered) {
                    $order->update(['status' => 'delivered']);
                } elseif ($anyDelivered) {
                    $order->update(['status' => 'partial_delivered']);
                }
            }
        });

        session()->flash('success', 'Salida de almacén registrada y lotes actualizados.');

        if ($this->order) {
            $this->redirect(route('sales.orders.show', $this->order), navigate: true);
        } else {
            $this->redirect(route('inventory.movements.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.sales.delivery-form', [
            'warehouses' => Warehouse::where('is_active', true)->with('branch')->orderBy('name')->get(),
            'reasons'    => SaleDelivery::REASONS,
        ]);
    }
}
