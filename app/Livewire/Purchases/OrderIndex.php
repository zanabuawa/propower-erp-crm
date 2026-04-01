<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OrderIndex extends Component
{
    use WithPagination;

    public string $tab = 'orders';
    public string $search = '';
    public string $filterStatus = '';
    public string $searchReq = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingSearchReq(): void { $this->resetPage(); }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $orders = PurchaseOrder::query()
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where('folio', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->with(['supplier', 'createdBy', 'branch'])
            ->withCount('items')
            ->latest()
            ->paginate(15);

        $requisitions = PurchaseRequisition::query()
            ->where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['authorized', 'ordered'])
            ->whereHas('finalQuotation', fn($q) => $q->where('status', 'authorized'))
            ->when($this->searchReq, fn($q) => $q
                ->where('folio', 'like', "%{$this->searchReq}%")
                ->orWhere('justification', 'like', "%{$this->searchReq}%"))
            ->with(['requestedBy', 'branch', 'finalQuotation.items', 'order'])
            ->latest()
            ->paginate(15, ['*'], 'reqPage');

        return view('livewire.purchases.order-index', compact('orders', 'requisitions'));
    }
}