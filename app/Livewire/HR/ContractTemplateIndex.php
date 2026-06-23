<?php

namespace App\Livewire\HR;

use App\Models\HrContractTemplate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Plantillas de Contrato')]
class ContractTemplateIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        HrContractTemplate::ensureDefaultsForCompany(auth()->user()->company_id, auth()->id());
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $template = HrContractTemplate::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);

        session()->flash('success', 'Plantilla actualizada correctamente.');
    }

    public function render()
    {
        $templates = HrContractTemplate::query()
            ->when($this->search, fn ($query) => $query->where(function ($subquery) {
                $subquery->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->paginate(12);

        return view('livewire.hr.contract-template-index', compact('templates'));
    }
}
