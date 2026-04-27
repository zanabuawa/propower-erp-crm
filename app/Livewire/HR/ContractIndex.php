<?php

namespace App\Livewire\HR;

use App\Models\HrContract;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Contratos')]
class ContractIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $contracts = HrContract::with(['employee', 'createdBy'])
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        return view('livewire.hr.contract-index', compact('contracts'));
    }
}
