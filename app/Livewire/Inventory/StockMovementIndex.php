<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class StockMovementIndex extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterType    = '';
    public string $filterStatus  = '';
    public string $filterWarehouse = '';
    public string $filterUser    = '';
    public string $dateFrom      = '';
    public string $dateTo        = '';

    public function updatingSearch(): void          { $this->resetPage(); }
    public function updatingFilterType(): void      { $this->resetPage(); }
    public function updatingFilterStatus(): void    { $this->resetPage(); }
    public function updatingFilterWarehouse(): void { $this->resetPage(); }
    public function updatingFilterUser(): void      { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $movements = StockMovement::query()
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q
                ->where(fn($q2) => $q2
                    ->where('folio', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%")))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterWarehouse, fn($q) => $q->where(function ($q2) {
                $q2->where('warehouse_id', $this->filterWarehouse)
                   ->orWhere('warehouse_destination_id', $this->filterWarehouse);
            }))
            ->when($this->filterUser, fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->dateFrom, fn($q) => $q->whereDate('moved_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('moved_at', '<=', $this->dateTo))
            ->with(['warehouse', 'warehouseDestination', 'user'])
            ->withCount('items')
            ->latest('moved_at')
            ->paginate(15);

        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $users      = User::where('company_id', $companyId)->orderBy('name')->get();

        return view('livewire.inventory.stock-movement-index', compact('movements', 'warehouses', 'users'));
    }
}
