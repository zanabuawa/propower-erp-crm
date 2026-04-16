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

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $employee_id = null;
    public string $type = 'tardanza';
    public string $incident_date = '';
    public string $description = '';
    public string $severity = 'low';
    public string $action_taken = '';
    public bool $resolved = false;

    public function updatingFilterSeverity(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['editingId','employee_id','type','description','severity','action_taken','resolved']);
        $this->type          = 'tardanza';
        $this->severity      = 'low';
        $this->incident_date = now()->format('Y-m-d');
        $this->showModal     = true;
    }

    public function openEdit(int $id): void
    {
        $inc = HrIncident::findOrFail($id);
        $this->editingId     = $id;
        $this->employee_id   = $inc->employee_id;
        $this->type          = $inc->type;
        $this->incident_date = $inc->incident_date->format('Y-m-d');
        $this->description   = $inc->description;
        $this->severity      = $inc->severity;
        $this->action_taken  = $inc->action_taken ?? '';
        $this->resolved      = $inc->resolved;
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate([
            'employee_id'   => 'required|exists:hr_employees,id',
            'type'          => 'required|in:' . implode(',', array_keys(HrIncident::TYPES)),
            'incident_date' => 'required|date',
            'description'   => 'required|string|min:10',
            'severity'      => 'required|in:' . implode(',', array_keys(HrIncident::SEVERITIES)),
        ]);

        $data = [
            'company_id'    => auth()->user()->company_id,
            'employee_id'   => $this->employee_id,
            'type'          => $this->type,
            'incident_date' => $this->incident_date,
            'description'   => $this->description,
            'severity'      => $this->severity,
            'action_taken'  => $this->action_taken ?: null,
            'resolved'      => $this->resolved,
            'resolved_at'   => $this->resolved ? now() : null,
            'created_by'    => auth()->id(),
        ];

        if ($this->editingId) {
            HrIncident::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Incidencia actualizada.');
        } else {
            HrIncident::create($data);
            session()->flash('success', 'Incidencia registrada.');
        }

        $this->showModal = false;
    }

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
