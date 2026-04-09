<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InventoryTransferIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function render()
    {
        $transfers = StockMovement::with(['warehouse', 'warehouseDestination', 'user'])
            ->withCount('items')
            ->where('company_id', auth()->user()->company_id)
            ->where('type', 'transfer')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('reference', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom, fn($q) => $q->whereDate('moved_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('moved_at', '<=', $this->dateTo))
            ->orderByDesc('moved_at')
            ->paginate(20);

        return view('livewire.inventory.inventory-transfer-index', compact('transfers'));
    }
}
