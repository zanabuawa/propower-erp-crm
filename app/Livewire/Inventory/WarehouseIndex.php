<?php

namespace App\Livewire\Inventory;

use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class WarehouseIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->confirmingDelete = false;
    }

    public function delete(): void
    {
        Warehouse::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Almacén eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.inventory.warehouse-index', [
            'warehouses' => Warehouse::query()
                ->where('is_transit', false)
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%"))
                ->with('branch')
                ->latest()
                ->paginate(15),
        ]);
    }
}