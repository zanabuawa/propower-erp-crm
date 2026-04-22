<?php

namespace App\Livewire\Projects;

use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\Project;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectTimeTracking extends Component
{
    public Project $project;

    public string $filterFrom = '';
    public string $filterTo   = '';
    public string $filterEmployee = '';

    public function mount(Project $project): void
    {
        $this->project    = $project;
        $this->filterFrom = $project->start_date?->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d');
        $this->filterTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $query = HrAttendance::with('employee')
            ->where('project_id', $this->project->id)
            ->whereNotNull('worked_hours')
            ->when($this->filterFrom, fn($q) => $q->where('date', '>=', $this->filterFrom))
            ->when($this->filterTo,   fn($q) => $q->where('date', '<=', $this->filterTo))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee));

        $records = $query->orderByDesc('date')->get();

        // Summary totals
        $totalHours    = $records->sum('worked_hours');
        $totalOvertime = $records->sum('overtime_hours');
        $totalDays     = $records->groupBy('date')->count();
        $employeeCount = $records->groupBy('employee_id')->count();

        // By employee summary
        $byEmployee = $records->groupBy('employee_id')->map(function ($rows) {
            $emp   = $rows->first()->employee;
            $hours = $rows->sum('worked_hours');
            $ot    = $rows->sum('overtime_hours');

            // Get cost_per_hour from project_employees pivot
            $pivotCost = $emp?->projects()
                ->where('projects.id', $rows->first()->project_id)
                ->first()?->pivot->cost_per_hour ?? 0;

            return [
                'employee'  => $emp,
                'days'      => $rows->count(),
                'hours'     => round($hours, 2),
                'overtime'  => round($ot, 2),
                'cost'      => round($hours * $pivotCost, 2),
                'rate'      => $pivotCost,
            ];
        })->sortByDesc('hours')->values();

        // By week
        $byWeek = $records->groupBy(fn($r) => Carbon::parse($r->date)->startOfWeek()->format('Y-m-d'))
            ->map(fn($rows, $week) => [
                'week'  => Carbon::parse($week)->translatedFormat('d M') . ' – ' . Carbon::parse($week)->endOfWeek()->translatedFormat('d M Y'),
                'hours' => round($rows->sum('worked_hours'), 2),
                'days'  => $rows->groupBy('date')->count(),
            ])->values();

        $totalLaborCost = $byEmployee->sum('cost');

        $employees = HrEmployee::whereHas('attendances', fn($q) =>
            $q->where('project_id', $this->project->id)
        )->orderBy('last_name')->get(['id', 'first_name', 'last_name']);

        return view('livewire.projects.project-time-tracking', compact(
            'records', 'byEmployee', 'byWeek',
            'totalHours', 'totalOvertime', 'totalDays', 'employeeCount',
            'totalLaborCost', 'employees'
        ));
    }
}
