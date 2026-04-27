<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrIncident;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Incidencias')]
class IncidentIndex extends Component
{
    use WithPagination;

    public string $filterSeverity = '';
    public string $filterType = '';
    public string $filterEmployee = '';
    public bool $filterResolved = false;

    public function updatingFilterSeverity(): void { $this->resetPage(); }

    public function markResolved(int $id): void
    {
        HrIncident::findOrFail($id)->update([
            'resolved'    => true,
            'resolved_at' => now(),
        ]);
        session()->flash('success', 'Incidencia marcada como resuelta.');
    }

    public function render()
    {
        $incidents = HrIncident::with(['employee', 'createdBy'])
            ->when($this->filterSeverity, fn($q) => $q->where('severity', $this->filterSeverity))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when(!$this->filterResolved, fn($q) => $q->where('resolved', false))
            ->latest('incident_date')
            ->paginate(15);

        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.incident-index', compact('incidents', 'employees'));
    }
}
