<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Control de Asistencias')]
class AttendanceIndex extends Component
{
    use WithPagination;

    public string $filterDate = '';
    public string $filterEmployee = '';
    public string $filterStatus = '';
    public string $filterProject = '';

    public function mount(): void
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function updatingFilterDate(): void { $this->resetPage(); }
    public function updatingFilterEmployee(): void { $this->resetPage(); }
    public function updatingFilterProject(): void { $this->resetPage(); }

    public function registerAllPresent(): void
    {
        $employees = HrEmployee::where('status', 'active')->get();
        $date = $this->filterDate ?: now()->format('Y-m-d');

        foreach ($employees as $emp) {
            HrAttendance::firstOrCreate(
                ['employee_id' => $emp->id, 'date' => $date],
                [
                    'company_id'  => auth()->user()->company_id,
                    'project_id'  => $this->filterProject ?: null,
                    'status'      => 'present',
                    'recorded_by' => auth()->id(),
                ]
            );
        }
        session()->flash('success', 'Asistencia masiva registrada para ' . $employees->count() . ' empleados.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $attendances = HrAttendance::with(['employee', 'project'])
            ->where('company_id', $companyId)
            ->when($this->filterDate,    fn($q) => $q->where('date', $this->filterDate))
            ->when($this->filterEmployee,fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when($this->filterStatus,  fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterProject, fn($q) => $q->where('project_id', $this->filterProject))
            ->orderBy('date', 'desc')
            ->paginate(20);

        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name']);

        $branchIds = Branch::where('company_id', $companyId)->pluck('id');
        $projects = Project::whereIn('branch_id', $branchIds)
            ->whereIn('status', ['activo', 'pausado'])
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $summary = [];
        if ($this->filterDate) {
            $summary = HrAttendance::where('company_id', $companyId)
                ->where('date', $this->filterDate)
                ->when($this->filterProject, fn($q) => $q->where('project_id', $this->filterProject))
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        }

        // Hours summary by project for the selected date
        $projectHours = null;
        if ($this->filterDate) {
            $projectHours = HrAttendance::with('project')
                ->where('company_id', $companyId)
                ->where('date', $this->filterDate)
                ->whereNotNull('project_id')
                ->whereNotNull('worked_hours')
                ->selectRaw('project_id, sum(worked_hours) as total_hours, count(*) as employees')
                ->groupBy('project_id')
                ->get();
        }

        return view('livewire.hr.attendance-index',
            compact('attendances', 'employees', 'projects', 'summary', 'projectHours'));
    }
}
