<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OrderIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function render()
    {
        return view('livewire.purchases.order-index', [
            'orders' => PurchaseOrder::query()
                ->where('company_id', auth()->user()->company_id)
                ->when($this->search, fn($q) => $q
                    ->where('folio', 'like', "%{$this->search}%"))
                ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                ->with(['supplier', 'createdBy', 'branch'])
                ->withCount('items')
                ->latest()
                ->paginate(15),
        ]);
    }
}