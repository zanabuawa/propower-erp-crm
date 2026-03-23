<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class StockMovementIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function render()
    {
        return view('livewire.inventory.stock-movement-index', [
            'movements' => StockMovement::query()
                ->where('company_id', auth()->user()->company_id)
                ->when($this->search, fn($q) => $q
                    ->where('folio', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%"))
                ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
                ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                ->when($this->dateFrom, fn($q) => $q->whereDate('moved_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($q) => $q->whereDate('moved_at', '<=', $this->dateTo))
                ->with(['warehouse', 'warehouseDestination', 'user'])
                ->withCount('items')
                ->latest('moved_at')
                ->paginate(15),
        ]);
    }
}