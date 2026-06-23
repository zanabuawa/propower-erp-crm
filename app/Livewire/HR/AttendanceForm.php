<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Asistencia')]
class AttendanceForm extends Component
{
    public ?HrAttendance $attendance = null;

    public ?int $employee_id = null;
    public ?int $project_id = null;
    public string $date = '';
    public string $check_in = '';
    public string $check_out = '';
    public string $status = 'present';
    public string $notes = '';

    public function mount(?HrAttendance $attendance = null, ?string $defaultDate = null): void
    {
        $this->date = $defaultDate ?: now()->format('Y-m-d');

        if ($attendance && $attendance->exists) {
            $this->attendance  = $attendance;
            $this->employee_id = $attendance->employee_id;
            $this->project_id  = $attendance->project_id;
            $this->date        = $attendance->date->format('Y-m-d');
            $this->check_in    = $attendance->check_in ? substr($attendance->check_in, 0, 5) : '';
            $this->check_out   = $attendance->check_out ? substr($attendance->check_out, 0, 5) : '';
            $this->status      = $attendance->status;
            $this->notes       = $attendance->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:hr_employees,id',
            'project_id'  => 'nullable|exists:projects,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i|after:check_in',
            'status'      => 'required|in:' . implode(',', array_keys(HrAttendance::STATUSES)),
            'notes'       => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $workedHours = null;
        if ($this->check_in && $this->check_out) {
            $workedHours = HrAttendance::calculateWorkedHours($this->date, $this->check_in, $this->check_out);
        }

        $data = [
            'company_id'   => auth()->user()->company_id,
            'employee_id'  => $this->employee_id,
            'project_id'   => $this->project_id,
            'date'         => $this->date,
            'check_in'     => $this->check_in ?: null,
            'check_out'    => $this->check_out ?: null,
            'worked_hours' => $workedHours,
            'status'       => $this->status,
            'notes'        => $this->notes ?: null,
            'recorded_by'  => auth()->id(),
        ];

        if ($this->attendance && $this->attendance->exists) {
            $this->attendance->update($data);
            session()->flash('success', 'Asistencia actualizada correctamente.');
        } else {
            HrAttendance::updateOrCreate(
                ['employee_id' => $this->employee_id, 'date' => $this->date],
                $data
            );
            session()->flash('success', 'Asistencia registrada correctamente.');
        }

        $this->redirect(route('hr.attendances.index', ['filterDate' => $this->date]), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name']);

        $branchIds = Branch::where('company_id', $companyId)->pluck('id');
        $projects = Project::whereIn('branch_id', $branchIds)
            ->whereIn('status', ['activo', 'pausado'])
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('livewire.hr.attendance-form', compact('employees', 'projects'));
    }
}
