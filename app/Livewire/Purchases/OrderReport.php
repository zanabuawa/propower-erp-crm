<?php

namespace App\Livewire\Purchases;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class OrderReport extends Component
{
    use WithPagination;

    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $status     = '';
    public ?int   $supplierId = null;
    public ?int   $branchId   = null;
    public string $currency   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function updatingDateFrom():  void { $this->resetPage(); }
    public function updatingDateTo():    void { $this->resetPage(); }
    public function updatingStatus():    void { $this->resetPage(); }
    public function updatingSupplierId():void { $this->resetPage(); }
    public function updatingBranchId():  void { $this->resetPage(); }
    public function updatingCurrency():  void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['status', 'supplierId', 'branchId', 'currency']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
        $this->resetPage();
    }

    private function baseQuery()
    {
        $companyId = auth()->user()->company_id;

        return PurchaseOrder::with(['supplier', 'branch', 'createdBy'])
            ->where('company_id', $companyId)
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->status,     fn($q) => $q->where('status', $this->status))
            ->when($this->supplierId, fn($q) => $q->where('supplier_id', $this->supplierId))
            ->when($this->branchId,   fn($q) => $q->where('branch_id', $this->branchId))
            ->when($this->currency,   fn($q) => $q->where('currency', $this->currency));
    }

    public function getTotalsProperty(): array
    {
        $rows = $this->baseQuery()->get(['subtotal', 'tax', 'total', 'status']);

        return [
            'count'    => $rows->count(),
            'subtotal' => $rows->sum(fn($r) => (float) $r->subtotal),
            'tax'      => $rows->sum(fn($r) => (float) $r->tax),
            'total'    => $rows->sum(fn($r) => (float) $r->total),
            'by_status'=> $rows->groupBy('status')->map->count(),
        ];
    }

    public function render()
    {
        $orders    = $this->baseQuery()->latest()->paginate(20);
        $suppliers = Supplier::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $branches  = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $statuses  = \App\Models\PurchaseOrder::STATUS;

        return view('livewire.purchases.order-report', compact('orders', 'suppliers', 'branches', 'statuses'));
    }
}
