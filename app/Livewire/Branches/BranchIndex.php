<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BranchIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        Branch::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Sucursal eliminada correctamente.');
    }

    public function render()
    {
        return view('livewire.branches.branch-index', [
            'branches' => Branch::query()
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%"))
                ->with('company')
                ->withCount('users')
                ->latest()
                ->paginate(15),
        ]);
    }
}