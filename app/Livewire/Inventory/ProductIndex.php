<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
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
        Product::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Producto eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.inventory.product-index', [
            'products' => Product::query()
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%"))
                ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
                ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', $this->filterStatus))
                ->with(['category', 'unitOfMeasure', 'primaryImage', 'stocks'])
                ->latest()
                ->paginate(15),
            'categories' => \App\Models\Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}