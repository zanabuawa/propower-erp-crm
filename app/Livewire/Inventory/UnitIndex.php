<?php

namespace App\Livewire\Inventory;

use App\Models\UnitOfMeasure;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UnitIndex extends Component
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
        UnitOfMeasure::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Unidad eliminada correctamente.');
    }

    public function render()
    {
        return view('livewire.inventory.unit-index', [
            'units' => UnitOfMeasure::query()
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('abbreviation', 'like', "%{$this->search}%"))
                ->withCount('products')
                ->latest()
                ->paginate(15),
        ]);
    }
}