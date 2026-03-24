<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

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
        Supplier::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Proveedor eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-index', [
            'suppliers' => Supplier::query()
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('rfc', 'like', "%{$this->search}%"))
                ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
                ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
                ->with(['phones', 'emails', 'assignedTo'])
                ->withCount('contacts', 'notes')
                ->latest()
                ->paginate(15),
        ]);
    }
}
