<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseReceipt;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class GoodsReceiptIndex extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterType    = '';
    public string $filterWarehouse = '';

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingFilterType(): void    { $this->resetPage(); }
    public function updatingFilterWarehouse(): void { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // ── Pendientes: transferencias en tránsito dirigidas a almacenes de la empresa ──
        $warehouseIds = Warehouse::where('company_id', $companyId)
            ->where('is_active', true)
            ->pluck('id');

        $pendingQuery = StockMovement::with(['warehouse', 'warehouseDestination', 'user', 'items.product'])
            ->where('company_id', $companyId)
            ->where('type', 'transfer')
            ->whereIn('status', ['requested', 'in_transit', 'partially_received'])
            ->whereIn('warehouse_destination_id', $warehouseIds);

        if ($this->search) {
            $pendingQuery->where(fn($q) => $q
                ->where('folio', 'like', "%{$this->search}%")
                ->orWhere('reference', 'like', "%{$this->search}%"));
        }

        if ($this->filterWarehouse) {
            $pendingQuery->where('warehouse_destination_id', $this->filterWarehouse);
        }

        $pending = $pendingQuery->latest('moved_at')->get();

        // ── Completadas: todos los PurchaseReceipt ────────────────────────────
        $receiptsQuery = PurchaseReceipt::with([
            'receivedBy',
            'warehouse',
            'order.supplier',
            'originMovement.warehouse',
            'originMovement.user',
            'items.product',
        ])
            ->where('company_id', $companyId);

        if ($this->search) {
            $receiptsQuery->where(fn($q) => $q
                ->where('folio', 'like', "%{$this->search}%")
                ->orWhere('notes', 'like', "%{$this->search}%"));
        }

        if ($this->filterType) {
            $receiptsQuery->where('reception_type', $this->filterType);
        }

        if ($this->filterWarehouse) {
            $receiptsQuery->where('warehouse_id', $this->filterWarehouse);
        }

        $receipts = $receiptsQuery->latest('received_at')->paginate(20);

        return view('livewire.purchases.goods-receipt-index', [
            'pending'    => $pending,
            'receipts'   => $receipts,
            'warehouses' => Warehouse::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'types'      => PurchaseReceipt::RECEPTION_TYPES,
        ]);
    }
}
