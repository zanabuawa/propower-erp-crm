<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrIncident;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Incidencia')]
class IncidentForm extends Component
{
    public ?HrIncident $incident = null;

    public ?int $employee_id = null;
    public string $type = 'tardanza';
    public string $incident_date = '';
    public string $description = '';
    public string $severity = 'low';
    public string $action_taken = '';
    public bool $resolved = false;

    public function mount(?HrIncident $incident = null): void
    {
        $this->incident_date = now()->format('Y-m-d');

        if ($incident && $incident->exists) {
            $this->incident      = $incident;
            $this->employee_id   = $incident->employee_id;
            $this->type          = $incident->type;
            $this->incident_date = $incident->incident_date->format('Y-m-d');
            $this->description   = $incident->description;
            $this->severity      = $incident->severity;
            $this->action_taken  = $incident->action_taken ?? '';
            $this->resolved      = $incident->resolved;
        }
    }

    public function rules(): array
    {
        return [
            'employee_id'   => 'required|exists:hr_employees,id',
            'type'          => 'required|in:' . implode(',', array_keys(HrIncident::TYPES)),
            'incident_date' => 'required|date',
            'description'   => 'required|string|min:10',
            'severity'      => 'required|in:' . implode(',', array_keys(HrIncident::SEVERITIES)),
            'action_taken'  => 'nullable|string',
            'resolved'      => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

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

        if ($this->incident && $this->incident->exists) {
            $this->incident->update($data);
            session()->flash('success', 'Incidencia actualizada correctamente.');
        } else {
            HrIncident::create($data);
            session()->flash('success', 'Incidencia registrada correctamente.');
        }

        $this->redirect(route('hr.incidents.index'), navigate: true);
    }

    public function render()
    {
        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.incident-form', compact('employees'));
    }
}
