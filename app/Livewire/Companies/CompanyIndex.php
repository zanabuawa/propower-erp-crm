<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Empresas')]
class CompanyIndex extends Component
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
        Company::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Empresa eliminada correctamente.');
    }

    public function render()
    {
        return view('livewire.companies.company-index', [
            'companies' => Company::query()
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('rfc', 'like', "%{$this->search}%"))
                ->withCount('branches', 'users')
                ->latest()
                ->paginate(15),
        ])->layout('layouts.app', ['title' => 'Empresas']);
    }
}
