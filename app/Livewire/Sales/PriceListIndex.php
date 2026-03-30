<?php

namespace App\Livewire\Sales;

use App\Models\PriceList;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PriceListIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

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
        PriceList::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Lista eliminada correctamente.');
    }

    public function render()
    {
        return view('livewire.sales.price-list-index', [
            'priceLists' => PriceList::query()
                ->where('company_id', auth()->user()->company_id)
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->withCount('items')
                ->latest()
                ->paginate(15),
        ]);
    }
}