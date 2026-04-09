<?php

namespace App\Livewire\Inventory;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\InventoryTransferNotification;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class InventoryTransferForm extends Component
{
    // ── Core state ────────────────────────────────────────────────────────────
    public ?StockMovement $stockMovement = null;
    public ?int $warehouse_id = null;
    public ?int $warehouse_destination_id = null;
    public string $reference = '';
    public string $notes = '';
    public string $moved_at = '';

    // ── Items (requested + late additions) ───────────────────────────────────
    // Each row: product_id, product_name, sku, quantity, stock_origen,
    //           received_quantity, received_at, is_late_addition, added_at, item_id (db id)
    public array $items = [];

    // ── Receipt modal ────────────────────────────────────────────────────────
    public bool $confirmReceiveAll = false;

    // ── Mode ──────────────────────────────────────────────────────────────────
    // 'create' | 'receive' | 'readonly'
    public string $mode = 'create';

    public function mount(?StockMovement $stockMovement = null): void
    {
        $this->moved_at = now()->format('Y-m-d\TH:i');

        if ($stockMovement && $stockMovement->exists) {
            $this->stockMovement = $stockMovement->load('items.product');
            $this->warehouse_id               = $stockMovement->warehouse_id;
            $this->warehouse_destination_id   = $stockMovement->warehouse_destination_id;
            $this->reference                  = $stockMovement->reference ?? '';
            $this->notes                      = $stockMovement->notes ?? '';
            $this->moved_at                   = $stockMovement->moved_at->format('Y-m-d\TH:i');

            $this->mode = $stockMovement->isEditable() ? 'receive' : 'readonly';

            $this->items = $stockMovement->items->map(function ($item) {
                return [
                    'item_id'           => $item->id,
                    'product_id'        => $item->product_id,
                    'product_name'      => $item->product->name,
                    'sku'               => $item->product->sku ?? '',
                    'quantity'          => (float) $item->quantity,
                    'stock_origen'      => (float) (Stock::where('product_id', $item->product_id)
                                              ->where('warehouse_id', $this->warehouse_id)
                                              ->value('quantity') ?? 0),
                    'received_quantity' => $item->received_quantity !== null
                                              ? (float) $item->received_quantity
                                              : null,
                    'received_at'       => $item->received_at?->format('Y-m-d') ?? '',
                    'is_late_addition'  => (bool) $item->is_late_addition,
                    'added_at'          => $item->added_at?->format('Y-m-d H:i') ?? null,
                ];
            })->toArray();
        }
    }

    // ── Catalog / product-picked ──────────────────────────────────────────────

    public function updatedWarehouseId(): void
    {
        $this->items = [];
    }

    #[On('product-picked')]
    public function addFromCatalog(int $productId, float $qty = 1): void
    {
        if (collect($this->items)->firstWhere('product_id', $productId)) return;

        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $this->warehouse_id)
            ->first();

        if (!$stock) return;

        $row = [
            'item_id'           => null,
            'product_id'        => $productId,
            'product_name'      => $stock->product->name,
            'sku'               => $stock->product->sku ?? '',
            'quantity'          => $qty,
            'stock_origen'      => (float) $stock->quantity,
            'received_quantity' => null,
            'received_at'       => '',
            'is_late_addition'  => false,
            'added_at'          => null,
        ];

        // If a transfer already exists → this is a late addition, save immediately
        if ($this->stockMovement && $this->stockMovement->exists) {
            $now = now();
            $dbItem = $this->stockMovement->items()->create([
                'product_id'               => $productId,
                'warehouse_id'             => $this->warehouse_id,
                'warehouse_destination_id' => $this->warehouse_destination_id,
                'quantity'                 => $qty,
                'unit_price'               => 0,
                'quantity_before'          => $stock->quantity,
                'quantity_after'           => $stock->quantity,
                'is_late_addition'         => true,
                'added_at'                 => $now,
            ]);
            $row['item_id']          = $dbItem->id;
            $row['is_late_addition'] = true;
            $row['added_at']         = $now->format('Y-m-d H:i');
        }

        $this->items[] = $row;
    }

    public function removeItem(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (!$item) return;

        // Only allow removing items not yet received
        if (!empty($item['received_quantity'])) return;

        // If late addition already saved to DB, delete it
        if ($item['item_id'] && $item['is_late_addition']) {
            StockMovementItem::find($item['item_id'])?->delete();
        }

        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function createRules(): array
    {
        return [
            'warehouse_id'             => 'required|exists:warehouses,id',
            'warehouse_destination_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'reference'                => 'nullable|string|max:255',
            'notes'                    => 'nullable|string',
            'moved_at'                 => 'required|date',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
        ];
    }

    public function save(): void
    {
        $this->validate($this->createRules());

        DB::transaction(function () {
            $folio = 'MOV-T-' . str_pad(
                StockMovement::where('company_id', auth()->user()->company_id)
                    ->where('type', 'transfer')->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $this->stockMovement = StockMovement::create([
                'company_id'               => auth()->user()->company_id,
                'warehouse_id'             => $this->warehouse_id,
                'warehouse_destination_id' => $this->warehouse_destination_id,
                'user_id'                  => auth()->id(),
                'type'                     => 'transfer',
                'folio'                    => $folio,
                'status'                   => 'requested',
                'reference'                => $this->reference ?: null,
                'notes'                    => $this->notes ?: null,
                'moved_at'                 => $this->moved_at,
            ]);

            $now = now();
            foreach ($this->items as $item) {
                $stockQty = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $this->warehouse_id)
                    ->value('quantity') ?? 0;

                $this->stockMovement->items()->create([
                    'product_id'               => $item['product_id'],
                    'warehouse_id'             => $this->warehouse_id,
                    'warehouse_destination_id' => $this->warehouse_destination_id,
                    'quantity'                 => $item['quantity'],
                    'unit_price'               => 0,
                    'quantity_before'          => $stockQty,
                    'quantity_after'           => $stockQty,
                    'added_at'                 => $now,
                ]);
            }
        });

        $this->notifyInventoryManagers('created');

        session()->flash('success', 'Transferencia solicitada con folio ' . $this->stockMovement->folio);
        $this->redirect(route('inventory.transfers.show', $this->stockMovement), navigate: true);
    }

    // ── Receive ───────────────────────────────────────────────────────────────

    public function saveReceipt(): void
    {
        $this->validate([
            'items.*.received_quantity' => 'nullable|numeric|min:0',
            'items.*.received_at'       => 'nullable|date',
        ]);

        DB::transaction(function () {
            $allReceived = true;
            $anyReceived = false;

            foreach ($this->items as $item) {
                if (!$item['item_id']) continue;
                $recvQty = $item['received_quantity'] !== null && $item['received_quantity'] !== ''
                    ? (float) $item['received_quantity']
                    : null;
                $recvAt  = !empty($item['received_at']) ? $item['received_at'] : null;

                $dbItem = StockMovementItem::find($item['item_id']);
                if (!$dbItem) continue;

                // Only process newly received items
                $previouslyReceived = $dbItem->received_quantity !== null;

                if ($recvQty !== null && $recvQty > 0 && !$previouslyReceived) {
                    // Decrement origin stock
                    $srcStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $this->warehouse_id],
                        ['quantity' => 0]
                    );
                    $before = $srcStock->quantity;
                    $srcStock->decrement('quantity', $recvQty);

                    // Increment destination stock
                    $dstStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $this->warehouse_destination_id],
                        ['quantity' => 0]
                    );
                    $dstStock->increment('quantity', $recvQty);

                    $dbItem->update([
                        'received_quantity' => $recvQty,
                        'received_at'       => $recvAt ?? now(),
                        'quantity_before'   => $before,
                        'quantity_after'    => $srcStock->fresh()->quantity,
                    ]);

                    $anyReceived = true;
                } elseif ($recvQty === null || $recvQty == 0) {
                    $allReceived = false;
                }

                if ($recvQty === null || $recvQty == 0) {
                    if (!$previouslyReceived) $allReceived = false;
                }
            }

            // Re-evaluate status from DB
            $dbItems = $this->stockMovement->items()->get();
            $totalItems = $dbItems->count();
            $receivedItems = $dbItems->whereNotNull('received_quantity')->where('received_quantity', '>', 0)->count();

            $newStatus = match(true) {
                $receivedItems === 0               => 'requested',
                $receivedItems < $totalItems       => 'partially_received',
                default                            => 'completed',
            };

            $this->stockMovement->update(['status' => $newStatus]);
        });

        $status = $this->stockMovement->fresh()->status;
        $this->notifyInventoryManagers($status === 'completed' ? 'completed' : 'partial');

        session()->flash('success', $status === 'completed'
            ? 'Transferencia completada.'
            : 'Recepción parcial guardada.');

        $this->redirect(route('inventory.transfers.show', $this->stockMovement), navigate: true);
    }

    public function markInTransit(): void
    {
        $this->stockMovement->update(['status' => 'in_transit']);
        $this->notifyInventoryManagers('in_transit');
        $this->mode = 'receive';
        session()->flash('success', 'Transferencia marcada como en tránsito.');
    }

    public function cancelTransfer(): void
    {
        $this->stockMovement->update(['status' => 'cancelled']);
        $this->notifyInventoryManagers('cancelled');
        session()->flash('success', 'Transferencia cancelada.');
        $this->redirect(route('inventory.transfers.index'), navigate: true);
    }

    // ── Late-addition qty update ───────────────────────────────────────────────

    public function updateLateItemQty(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (!$item || !$item['item_id'] || !$item['is_late_addition']) return;

        StockMovementItem::find($item['item_id'])?->update([
            'quantity' => $item['quantity'],
        ]);
    }

    // ── Notifications ────────────────────────────────────────────────────────

    private function notifyInventoryManagers(string $eventType): void
    {
        $movement = $this->stockMovement;
        $companyId = auth()->user()->company_id;

        $titles = [
            'created'    => 'Nueva transferencia solicitada',
            'in_transit' => 'Transferencia en tránsito',
            'partial'    => 'Recepción parcial registrada',
            'completed'  => 'Transferencia completada',
            'cancelled'  => 'Transferencia cancelada',
        ];
        $messages = [
            'created'    => "Folio {$movement->folio}: de {$movement->warehouse?->name} → {$movement->warehouseDestination?->name}",
            'in_transit' => "Folio {$movement->folio} marcada en tránsito.",
            'partial'    => "Folio {$movement->folio}: se recibieron artículos parcialmente.",
            'completed'  => "Folio {$movement->folio}: todos los artículos recibidos.",
            'cancelled'  => "Folio {$movement->folio} fue cancelada.",
        ];

        $notification = new InventoryTransferNotification(
            title: $titles[$eventType] ?? 'Transferencia actualizada',
            message: $messages[$eventType] ?? '',
            type: $eventType,
            transferId: $movement->id,
        );

        User::where('company_id', $companyId)
            ->where('id', '!=', auth()->id())
            ->get()
            ->filter(fn($u) => $u->can('adjust inventory') || $u->can('view inventory'))
            ->each(fn($u) => $u->notify($notification));
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.inventory.inventory-transfer-form', [
            'warehouses' => Warehouse::where('company_id', auth()->user()->company_id)
                ->where('is_active', true)
                ->with('branch')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
