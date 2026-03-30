<?php

namespace App\Livewire\Sales;

use App\Models\SaleOrder;
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
        return view('livewire.sales.order-index', [
            'orders' => SaleOrder::query()
                ->where('company_id', auth()->user()->company_id)
                ->when($this->search, fn($q) => $q
                    ->where('folio', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
                ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                ->with(['customer', 'createdBy'])
                ->withCount('items')
                ->latest()
                ->paginate(15),
        ]);
    }
}