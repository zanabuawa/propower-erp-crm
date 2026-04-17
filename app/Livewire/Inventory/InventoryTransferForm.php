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

    // ── Items ─────────────────────────────────────────────────────────────────
    // Each row: item_id, product_id, product_name, sku, quantity, stock_origen,
    //           dispatched_quantity, received_quantity, received_at,
    //           is_late_addition, added_at
    public array $items = [];

    // ── Dispatch (origin warehouse response) ──────────────────────────────────
    public string $dispatchAction  = 'complete'; // complete | partial | reject
    public string $dispatchNotes   = '';
    public bool   $dispatchIsFinal = false;

    // ── Mode ──────────────────────────────────────────────────────────────────
    // 'create' | 'dispatch' | 'receive' | 'readonly'
    public string $mode = 'create';

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(?StockMovement $stockMovement = null): void
    {
        $this->moved_at = now()->format('Y-m-d\TH:i');

        if (!$stockMovement || !$stockMovement->exists) {
            return;
        }

        $this->stockMovement = $stockMovement->load(['items.product', 'warehouse', 'warehouseDestination']);

        $this->warehouse_id             = $stockMovement->warehouse_id;
        $this->warehouse_destination_id = $stockMovement->warehouse_destination_id;
        $this->reference                = $stockMovement->reference ?? '';
        $this->notes                    = $stockMovement->notes ?? '';
        $this->moved_at                 = $stockMovement->moved_at->format('Y-m-d\TH:i');

        $this->mode = $this->resolveMode($stockMovement);

        $this->items = $stockMovement->items->map(function ($item) {
            return [
                'item_id'             => $item->id,
                'product_id'          => $item->product_id,
                'product_name'        => $item->product->name,
                'sku'                 => $item->product->sku ?? '',
                'quantity'            => (float) $item->quantity,
                'stock_origen'        => (float) (Stock::where('product_id', $item->product_id)
                                            ->where('warehouse_id', $this->warehouse_id)
                                            ->value('quantity') ?? 0),
                'dispatched_quantity' => $item->dispatched_quantity !== null
                                            ? (float) $item->dispatched_quantity
                                            : (float) $item->quantity,
                'received_quantity'   => $item->received_quantity !== null
                                            ? (float) $item->received_quantity
                                            : null,
                'received_at'         => $item->received_at?->format('Y-m-d') ?? '',
                'is_late_addition'    => (bool) $item->is_late_addition,
                'added_at'            => $item->added_at?->format('Y-m-d H:i') ?? null,
            ];
        })->toArray();
    }

    // ── Mode resolution ───────────────────────────────────────────────────────

    private function resolveMode(StockMovement $movement): string
    {
        $status  = $movement->status;
        $isLocal = $this->isLocalTransfer($movement);
        $user    = auth()->user();

        $originBranchId = $movement->warehouse?->branch_id;
        $canManageOrigin = $user->hasRole(['admin', 'gerente'])
            || ($user->hasRole('almacenista') && $user->branch_id === $originBranchId);

        if ($status === 'requested') {
            if ($isLocal) {
                return 'receive'; // simplified flow: requester authorizes directly
            }
            return $canManageOrigin ? 'dispatch' : 'readonly';
        }

        // 'accepted': origin has approved but not yet physically sent
        if ($status === 'accepted') {
            return $canManageOrigin ? 'dispatch' : 'readonly';
        }

        if (in_array($status, ['in_transit', 'partially_received'])) {
            return $this->canReceive($movement, $user) ? 'receive' : 'readonly';
        }

        return 'readonly';
    }

    /**
     * Returns true if the user is allowed to register receipt at the destination.
     * Only: comprador (any) OR almacenista of the destination branch OR admin/gerente.
     */
    private function canReceive(StockMovement $movement, \App\Models\User $user): bool
    {
        if ($user->hasRole(['admin', 'gerente', 'comprador'])) {
            return true;
        }

        $destBranchId = $movement->warehouseDestination?->branch_id;

        return $user->hasRole('almacenista') && $user->branch_id === $destBranchId;
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
            'item_id'             => null,
            'product_id'          => $productId,
            'product_name'        => $stock->product->name,
            'sku'                 => $stock->product->sku ?? '',
            'quantity'            => $qty,
            'stock_origen'        => (float) $stock->quantity,
            'dispatched_quantity' => $qty,
            'received_quantity'   => null,
            'received_at'         => '',
            'is_late_addition'    => false,
            'added_at'            => null,
        ];

        if ($this->stockMovement && $this->stockMovement->exists) {
            $now    = now();
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

        if (!empty($item['received_quantity'])) return;

        if ($item['item_id'] && $item['is_late_addition']) {
            StockMovementItem::find($item['item_id'])?->delete();
        }

        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function save(): void
    {
        // Guard: warehouses are immutable once a transfer has been created
        if ($this->stockMovement?->exists) {
            session()->flash('error', 'No se puede modificar una transferencia ya creada.');
            return;
        }

        $this->validate([
            'warehouse_id'             => 'required|exists:warehouses,id',
            'warehouse_destination_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'reference'                => 'nullable|string|max:255',
            'notes'                    => 'nullable|string',
            'moved_at'                 => 'required|date',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
        ]);

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
        $this->logEvent('created');

        session()->flash('success', 'Transferencia solicitada con folio ' . $this->stockMovement->folio);
        $this->redirect(route('inventory.transfers.show', $this->stockMovement), navigate: true);
    }

    // ── Dispatch: origin warehouse response + stock movement to transit ────────

    public function submitDispatch(): void
    {
        // Guard: only allowed when transfer is in 'requested' state
        if (!$this->stockMovement || $this->stockMovement->status !== 'requested') {
            session()->flash('error', 'Esta transferencia ya no puede ser procesada en este estado.');
            return;
        }

        $rules = ['dispatchAction' => 'required|in:complete,partial,reject'];

        if ($this->dispatchAction === 'reject') {
            $rules['dispatchNotes'] = 'required|string|min:5|max:1000';
        } elseif ($this->dispatchAction === 'partial') {
            $rules['dispatchNotes']              = 'required|string|min:5|max:1000';
            $rules['items.*.dispatched_quantity'] = 'required|numeric|min:0';
        }

        $this->validate($rules, [
            'dispatchNotes.required' => $this->dispatchAction === 'reject'
                ? 'El motivo del rechazo es obligatorio.'
                : 'La justificación es obligatoria.',
            'dispatchNotes.min' => 'Debe ingresar al menos 5 caracteres.',
        ]);

        DB::transaction(function () {
            if ($this->dispatchAction === 'reject') {
                $this->stockMovement->update([
                    'status'         => 'rejected',
                    'dispatch_notes' => $this->dispatchNotes,
                    'dispatched_by'  => auth()->id(),
                ]);

                $this->notifyInventoryManagers('rejected');
                $this->logEvent('rejected', $this->dispatchNotes);
                session()->flash('success', 'Transferencia rechazada.');
                $this->redirect(route('inventory.transfers.index'), navigate: true);
                return;
            }

            // Acceptance: move stock origin → transit warehouse
            $companyId       = auth()->user()->company_id;
            $transitWarehouse = Warehouse::transitForCompany($companyId);
            $itemsSummary    = [];

            foreach ($this->items as $index => $item) {
                if (!$item['item_id']) continue;

                $dispatched = $this->dispatchAction === 'complete'
                    ? (float) $item['quantity']
                    : max(0, (float) $item['dispatched_quantity']);

                if ($dispatched > 0) {
                    // Decrement origin stock
                    $originStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $this->warehouse_id],
                        ['quantity' => 0]
                    );
                    $before = (float) $originStock->quantity;
                    $originStock->decrement('quantity', $dispatched);

                    // Increment transit warehouse stock
                    $transitStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $transitWarehouse->id],
                        ['quantity' => 0]
                    );
                    $transitStock->increment('quantity', $dispatched);
                }

                // Save dispatched_quantity and update quantity_before/after on item
                StockMovementItem::find($item['item_id'])?->update([
                    'dispatched_quantity' => $dispatched,
                    'quantity_before'     => $before ?? 0,
                    'quantity_after'      => isset($originStock) ? (float) $originStock->fresh()->quantity : 0,
                ]);

                $itemsSummary[] = [
                    'product'    => $item['product_name'],
                    'requested'  => $item['quantity'],
                    'dispatched' => $dispatched,
                ];
            }

            $isFinal = $this->dispatchAction === 'complete' || $this->dispatchIsFinal;

            $this->stockMovement->update([
                'status'            => 'accepted',   // awaiting physical send
                'dispatch_notes'    => $this->dispatchNotes ?: null,
                'dispatched_by'     => auth()->id(),
                'dispatch_is_final' => $isFinal,
            ]);

            $logAction = $this->dispatchAction === 'complete' ? 'accepted_complete' : 'accepted_partial';
            $event     = $this->dispatchAction === 'complete' ? 'dispatched_complete' : 'dispatched_partial';
            $this->notifyInventoryManagers($event, $itemsSummary);
            $this->logEvent($logAction, $this->dispatchNotes ?: null, ['items' => $itemsSummary]);

            session()->flash('success', $this->dispatchAction === 'complete'
                ? 'Aceptado. Stock reservado en Almacén de Transferencias. Marca como enviada cuando salga la mercancía.'
                : 'Aceptación parcial registrada. Marca como enviada cuando salga la mercancía.');

            $this->redirect(route('inventory.transfers.show', $this->stockMovement), navigate: true);
        });
    }

    // ── Mark as sent: accepted → in_transit ──────────────────────────────────

    public function markAsSent(): void
    {
        if (!$this->stockMovement || $this->stockMovement->status !== 'accepted') {
            session()->flash('error', 'Solo se puede marcar como enviada una transferencia en estado Aceptada.');
            return;
        }

        $this->stockMovement->update(['status' => 'in_transit']);
        $this->notifyInventoryManagers('sent');
        $this->logEvent('sent');

        $this->mode = 'readonly';
        session()->flash('success', 'Transferencia marcada como enviada. El solicitante ya puede registrar la recepción.');
        $this->redirect(route('inventory.transfers.show', $this->stockMovement), navigate: true);
    }

    // ── Local transfer: direct authorization + stock to transit ───────────────

    public function markInTransit(): void
    {
        if (!$this->stockMovement || $this->stockMovement->status !== 'requested') {
            session()->flash('error', 'Esta transferencia ya no puede ser autorizada en su estado actual.');
            return;
        }

        if (!$this->isLocalTransfer($this->stockMovement->loadMissing('warehouse', 'warehouseDestination'))) {
            session()->flash('error', 'Solo las transferencias locales pueden autorizarse directamente.');
            return;
        }

        DB::transaction(function () {
            $companyId        = auth()->user()->company_id;
            $transitWarehouse = Warehouse::transitForCompany($companyId);
            $itemsSummary     = [];

            $this->stockMovement->loadMissing('items');

            foreach ($this->stockMovement->items as $dbItem) {
                $qty = (float) $dbItem->quantity;

                if ($qty > 0) {
                    $originStock = Stock::firstOrCreate(
                        ['product_id' => $dbItem->product_id, 'warehouse_id' => $this->warehouse_id],
                        ['quantity' => 0]
                    );
                    $before = (float) $originStock->quantity;
                    $originStock->decrement('quantity', $qty);

                    $transitStock = Stock::firstOrCreate(
                        ['product_id' => $dbItem->product_id, 'warehouse_id' => $transitWarehouse->id],
                        ['quantity' => 0]
                    );
                    $transitStock->increment('quantity', $qty);

                    $dbItem->update([
                        'dispatched_quantity' => $qty,
                        'quantity_before'     => $before,
                        'quantity_after'      => (float) $originStock->fresh()->quantity,
                    ]);
                }

                $itemsSummary[] = [
                    'product'    => $dbItem->product?->name ?? '',
                    'requested'  => $qty,
                    'dispatched' => $qty,
                ];
            }

            $this->stockMovement->update([
                'status'            => 'in_transit',
                'dispatched_by'     => auth()->id(),
                'dispatch_is_final' => true,
            ]);

            $this->notifyInventoryManagers('in_transit', $itemsSummary);
            $this->logEvent('sent', null, ['items' => $itemsSummary]);
        });

        $this->mode = 'receive';
        session()->flash('success', 'Transferencia autorizada. Stock enviado al Almacén de Transferencias.');
    }

    // ── Receive: transit → destination ────────────────────────────────────────

    public function saveReceipt(): void
    {
        // Server-side guard: only comprador, dest almacenista, admin, gerente
        $movement = $this->stockMovement->loadMissing('warehouseDestination');
        if (!$this->canReceive($movement, auth()->user())) {
            session()->flash('error', 'No tienes permiso para registrar la recepción de esta transferencia.');
            return;
        }

        // Guard: only allowed when in transit or partially received
        if (!in_array($this->stockMovement->status, ['in_transit', 'partially_received'])) {
            session()->flash('error', 'Solo se puede registrar recepción cuando la transferencia está en tránsito.');
            return;
        }

        $this->validate([
            'items.*.received_quantity' => 'nullable|numeric|min:0',
            'items.*.received_at'       => 'nullable|date',
        ]);

        DB::transaction(function () {
            $companyId        = auth()->user()->company_id;
            $transitWarehouse = Warehouse::transitForCompany($companyId);

            foreach ($this->items as $item) {
                if (!$item['item_id']) continue;

                $recvQty = $item['received_quantity'] !== null && $item['received_quantity'] !== ''
                    ? (float) $item['received_quantity']
                    : null;
                $recvAt = !empty($item['received_at']) ? $item['received_at'] : null;

                $dbItem = StockMovementItem::find($item['item_id']);
                if (!$dbItem || $dbItem->received_quantity !== null) continue;

                if ($recvQty !== null && $recvQty > 0) {
                    // Decrement transit warehouse
                    $transitStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $transitWarehouse->id],
                        ['quantity' => 0]
                    );
                    $before = (float) $transitStock->quantity;
                    $transitStock->decrement('quantity', $recvQty);

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
                        'quantity_after'    => (float) $transitStock->fresh()->quantity,
                    ]);
                }
            }

            // Determine new status
            $dbItems         = $this->stockMovement->items()->get();
            $total           = $dbItems->count();
            $received        = $dbItems->whereNotNull('received_quantity')->where('received_quantity', '>', 0)->count();
            $isFinalDispatch = (bool) $this->stockMovement->dispatch_is_final;

            $newStatus = match(true) {
                $received === 0                         => 'in_transit',
                $received >= $total || $isFinalDispatch => 'completed',
                default                                 => 'partially_received',
            };

            $this->stockMovement->update(['status' => $newStatus]);
        });

        $status = $this->stockMovement->fresh()->status;
        $logAction = $status === 'completed' ? 'received_complete' : 'received_partial';
        $this->notifyInventoryManagers($status === 'completed' ? 'completed' : 'partial');
        $this->logEvent($logAction);

        session()->flash('success', $status === 'completed'
            ? 'Transferencia completada.'
            : 'Recepción parcial guardada.');

        $this->redirect(route('inventory.transfers.show', $this->stockMovement), navigate: true);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancelTransfer(): void
    {
        $cancellable = ['requested', 'accepted', 'in_transit', 'partially_received'];
        if (!$this->stockMovement || !in_array($this->stockMovement->status, $cancellable)) {
            session()->flash('error', 'Esta transferencia no puede cancelarse en su estado actual.');
            return;
        }

        // When accepted or in_transit: stock was already moved to transit → reverse it back to origin
        if (in_array($this->stockMovement->status, ['accepted', 'in_transit'])) {
            $companyId        = auth()->user()->company_id;
            $transitWarehouse = Warehouse::transitForCompany($companyId);

            DB::transaction(function () use ($transitWarehouse) {
                $this->stockMovement->loadMissing('items');

                foreach ($this->stockMovement->items as $dbItem) {
                    $dispatched = (float) ($dbItem->dispatched_quantity ?? 0);
                    if ($dispatched <= 0) continue;

                    $transitStock = Stock::where('product_id', $dbItem->product_id)
                        ->where('warehouse_id', $transitWarehouse->id)
                        ->first();
                    if ($transitStock) {
                        $transitStock->decrement('quantity', $dispatched);
                    }

                    $originStock = Stock::firstOrCreate(
                        ['product_id' => $dbItem->product_id, 'warehouse_id' => $this->warehouse_id],
                        ['quantity' => 0]
                    );
                    $originStock->increment('quantity', $dispatched);
                }

                $this->stockMovement->update(['status' => 'cancelled']);
            });
        } else {
            // requested: no stock was moved yet, just cancel
            $this->stockMovement->update(['status' => 'cancelled']);
        }

        $this->notifyInventoryManagers('cancelled');
        $this->logEvent('cancelled');
        session()->flash('success', 'Transferencia cancelada.');
        $this->redirect(route('inventory.transfers.index'), navigate: true);
    }

    // ── Late-addition qty update ──────────────────────────────────────────────

    public function updateLateItemQty(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (!$item || !$item['item_id'] || !$item['is_late_addition']) return;

        StockMovementItem::find($item['item_id'])?->update(['quantity' => $item['quantity']]);
    }

    // ── Notifications ─────────────────────────────────────────────────────────

    private function notifyInventoryManagers(string $eventType, array $itemsSummary = []): void
    {
        $movement  = $this->stockMovement;
        $companyId = auth()->user()->company_id;
        $isLocal   = $this->isLocalTransfer($movement);

        $titles = [
            'created'             => $isLocal ? 'Nueva transferencia local solicitada' : 'Nueva transferencia solicitada',
            'dispatched_complete' => 'Transferencia aceptada — pendiente de envío',
            'dispatched_partial'  => 'Transferencia aceptada parcialmente — pendiente de envío',
            'rejected'            => 'Transferencia rechazada',
            'sent'                => 'Mercancía enviada — pendiente de recepción',
            'in_transit'          => 'Transferencia en tránsito',
            'partial'             => 'Recepción parcial registrada en ' . ($movement->warehouseDestination?->name ?? 'destino'),
            'completed'           => 'Transferencia completada — inventario recibido',
            'cancelled'           => 'Transferencia cancelada',
        ];

        $notes    = $movement->dispatch_notes ? " Motivo: {$movement->dispatch_notes}" : '';
        $messages = [
            'created'             => "Folio {$movement->folio}: {$movement->warehouse?->name} → {$movement->warehouseDestination?->name}" . ($isLocal ? ' (local)' : ''),
            'dispatched_complete' => "Folio {$movement->folio}: aceptada completamente desde {$movement->warehouse?->name}. Stock reservado en tránsito.",
            'dispatched_partial'  => "Folio {$movement->folio}: aceptación parcial desde {$movement->warehouse?->name}.{$notes}",
            'rejected'            => "Folio {$movement->folio} fue rechazada por {$movement->warehouse?->name}.{$notes}",
            'sent'                => "Folio {$movement->folio}: mercancía enviada desde {$movement->warehouse?->name}. Ya puedes registrar la recepción.",
            'in_transit'          => "Folio {$movement->folio} autorizada y en tránsito.",
            'partial'             => "Folio {$movement->folio}: recepción parcial en {$movement->warehouseDestination?->name}.",
            'completed'           => "Folio {$movement->folio}: todos los artículos recibidos en {$movement->warehouseDestination?->name}.",
            'cancelled'           => "Folio {$movement->folio} fue cancelada.",
        ];

        $extraData = [];
        if (!empty($itemsSummary)) {
            $extraData['items_summary'] = $itemsSummary;
        }
        if ($movement->dispatch_notes) {
            $extraData['dispatch_notes'] = $movement->dispatch_notes;
        }

        $notification = new InventoryTransferNotification(
            title:      $titles[$eventType]   ?? 'Transferencia actualizada',
            message:    $messages[$eventType] ?? '',
            type:       $eventType,
            transferId: $movement->id,
            extraData:  $extraData,
        );

        $recipients = $this->resolveRecipients($eventType, $companyId, $movement, $isLocal);

        $recipients->where('id', '!=', auth()->id())
            ->each(fn($u) => $u->notify($notification));
    }

    private function isLocalTransfer(StockMovement $movement): bool
    {
        $originBranch = $movement->warehouse?->branch_id;
        $destBranch   = $movement->warehouseDestination?->branch_id;

        return $originBranch !== null && $originBranch === $destBranch;
    }

    /**
     * Resolve notification recipients by event type.
     *
     * created (inter-branch)               → almacenistas origen + comprador + admin + gerente
     * created (local)                      → comprador + admin + gerente
     * dispatched_complete/partial/rejected → solicitante + comprador + admin + gerente
     * in_transit                           → almacenistas destino + comprador + admin + gerente
     * partial / completed / cancelled      → admin + gerente + almacenistas
     */
    private function resolveRecipients(string $eventType, int $companyId, StockMovement $movement, bool $isLocal = false): \Illuminate\Database\Eloquent\Collection
    {
        $originBranchId = $movement->warehouse?->branch_id;
        $destBranchId   = $movement->warehouseDestination?->branch_id;

        // After dispatch decision OR physical send → solicitante + comprador + admin + gerente
        if (in_array($eventType, ['dispatched_complete', 'dispatched_partial', 'rejected', 'sent'])) {
            return User::where('company_id', $companyId)
                ->where(function ($q) use ($movement, $companyId) {
                    $q->where('id', $movement->user_id)
                      ->orWhere(function ($q2) use ($companyId) {
                          $q2->where('company_id', $companyId)
                             ->whereHas('roles', fn($r) => $r->whereIn('name', ['comprador', 'admin', 'gerente']));
                      });
                })
                ->get();
        }

        if ($eventType === 'created') {
            if ($isLocal) {
                return User::where('company_id', $companyId)
                    ->whereHas('roles', fn($r) => $r->whereIn('name', ['comprador', 'admin', 'gerente']))
                    ->get();
            }

            return User::where('company_id', $companyId)
                ->where(function ($q) use ($originBranchId) {
                    $q->whereHas('roles', fn($r) => $r->where('name', 'almacenista'))
                      ->when($originBranchId, fn($q2) => $q2->where('branch_id', $originBranchId));
                })
                ->orWhere(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->whereHas('roles', fn($r) => $r->whereIn('name', ['comprador', 'admin', 'gerente']));
                })
                ->get();
        }

        // in_transit: notify destination almacenistas + comprador + admin + gerente
        if ($eventType === 'in_transit') {
            return User::where('company_id', $companyId)
                ->where(function ($q) use ($companyId, $destBranchId) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['comprador', 'admin', 'gerente']))
                      ->orWhere(function ($q2) use ($destBranchId) {
                          $q2->whereHas('roles', fn($r) => $r->where('name', 'almacenista'))
                             ->when($destBranchId, fn($q3) => $q3->where('branch_id', $destBranchId));
                      });
                })
                ->get();
        }

        // partial / completed: solicitante + comprador + almacenista origen + admin + gerente
        if (in_array($eventType, ['partial', 'completed'])) {
            return User::where('company_id', $companyId)
                ->where(function ($q) use ($movement, $companyId, $originBranchId) {
                    $q->where('id', $movement->user_id) // solicitante
                      ->orWhere(function ($q2) use ($companyId, $originBranchId) {
                          // almacenistas del almacén origen
                          $q2->where('company_id', $companyId)
                             ->whereHas('roles', fn($r) => $r->where('name', 'almacenista'))
                             ->when($originBranchId, fn($q3) => $q3->where('branch_id', $originBranchId));
                      })
                      ->orWhere(function ($q2) use ($companyId) {
                          // comprador + admin + gerente
                          $q2->where('company_id', $companyId)
                             ->whereHas('roles', fn($r) => $r->whereIn('name', ['comprador', 'admin', 'gerente']));
                      });
                })
                ->get();
        }

        // cancelled: solicitante + comprador + origin almacenistas + admin + gerente
        if ($eventType === 'cancelled') {
            return User::where('company_id', $companyId)
                ->where(function ($q) use ($movement, $companyId, $originBranchId) {
                    $q->where('id', $movement->user_id)
                      ->orWhere(function ($q2) use ($companyId, $originBranchId) {
                          $q2->where('company_id', $companyId)
                             ->whereHas('roles', fn($r) => $r->where('name', 'almacenista'))
                             ->when($originBranchId, fn($q3) => $q3->where('branch_id', $originBranchId));
                      })
                      ->orWhere(function ($q2) use ($companyId) {
                          $q2->where('company_id', $companyId)
                             ->whereHas('roles', fn($r) => $r->whereIn('name', ['comprador', 'admin', 'gerente']));
                      });
                })
                ->get();
        }

        // Default: admin + gerente + all almacenistas
        return User::where('company_id', $companyId)
            ->whereHas('roles', fn($r) => $r->whereIn('name', ['admin', 'gerente', 'almacenista']))
            ->get();
    }

    // ── Event logging ────────────────────────────────────────────────────────

    private function logEvent(string $action, ?string $notes = null, array $data = []): void
    {
        $this->stockMovement->events()->create([
            'user_id' => auth()->id(),
            'action'  => $action,
            'notes'   => $notes ?: null,
            'data'    => $data ?: null,
        ]);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $warehouses = Warehouse::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where('is_transit', false)
            ->with('branch')
            ->orderBy('name')
            ->get();

        $isLocal = false;
        if ($this->stockMovement) {
            $isLocal = $this->isLocalTransfer(
                $this->stockMovement->loadMissing('warehouse', 'warehouseDestination')
            );
        } elseif ($this->warehouse_id && $this->warehouse_destination_id) {
            $originBranch = $warehouses->firstWhere('id', $this->warehouse_id)?->branch_id;
            $destBranch   = $warehouses->firstWhere('id', $this->warehouse_destination_id)?->branch_id;
            $isLocal      = $originBranch !== null && $originBranch === $destBranch;
        }

        return view('livewire.inventory.inventory-transfer-form', [
            'warehouses' => $warehouses,
            'isLocal'    => $isLocal,
        ]);
    }
}
