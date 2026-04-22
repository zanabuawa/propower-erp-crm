<?php

namespace App\Livewire\HR;

use App\Models\HrAttendance;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrPosition;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Planeación Organizacional')]
class WorkforcePlanning extends Component
{
    public string $activeTab = 'headcount'; // headcount | turnover | absenteeism
    public int    $year;
    public int    $month;

    public function mount(): void
    {
        $this->year  = now()->year;
        $this->month = now()->month;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        return view('livewire.hr.workforce-planning', [
            'headcountData'   => $this->headcountData($companyId),
            'turnoverData'    => $this->turnoverData($companyId),
            'absenteeismData' => $this->absenteeismData($companyId),
        ]);
    }

    // ── 1. Control de plantilla por puesto ────────────────────────────────────

    private function headcountData(int $companyId): array
    {
        $positions = HrPosition::with('department')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->withCount(['employees' => fn($q) => $q->where('status', 'active')])
            ->withCount(['employees as open_vacancies_count' => fn($q) =>
                $q->where('status', 'active') // placeholder — calculated below
            ])
            ->orderBy('name')
            ->get();

        // Active vacancies count per position
        $vacanciesByPosition = \App\Models\HrJobOpening::where('company_id', $companyId)
            ->where('status', 'open')
            ->selectRaw('position_id, sum(quantity) as total_vacancies')
            ->groupBy('position_id')
            ->pluck('total_vacancies', 'position_id');

        $rows = $positions->map(function ($pos) use ($vacanciesByPosition) {
            $filled    = $pos->employees_count;
            $auth      = $pos->authorized_headcount ?? 0;
            $gap       = $auth > 0 ? $auth - $filled : null;
            $openVacs  = $vacanciesByPosition[$pos->id] ?? 0;

            return [
                'id'          => $pos->id,
                'name'        => $pos->name,
                'department'  => $pos->department?->name ?? '—',
                'authorized'  => $auth,
                'filled'      => $filled,
                'gap'         => $gap,           // null = no definida; <0 = excede; >0 = vacante
                'open_vacs'   => $openVacs,
                'status'      => $auth === 0     ? 'undefined'
                               : ($filled > $auth ? 'over'
                               : ($filled === $auth ? 'complete' : 'under')),
            ];
        });

        return [
            'rows'      => $rows,
            'total_auth'=> $rows->sum('authorized'),
            'total_fill'=> $rows->sum('filled'),
            'over'      => $rows->where('status', 'over')->count(),
            'under'     => $rows->where('status', 'under')->count(),
            'complete'  => $rows->where('status', 'complete')->count(),
        ];
    }

    // ── 2. Rotación mensual (últimos 12 meses) ────────────────────────────────

    private function turnoverData(int $companyId): array
    {
        $months = collect(range(11, 0))->map(fn($i) => now()->subMonths($i));

        // Terminations per month
        $terminations = HrEmployee::where('company_id', $companyId)
            ->whereNotNull('termination_date')
            ->whereDate('termination_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(termination_date, '%Y-%m') as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        // New hires per month
        $hires = HrEmployee::where('company_id', $companyId)
            ->whereDate('hire_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(hire_date, '%Y-%m') as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = $months->map(fn($m) => $m->translatedFormat('M Y'))->values()->toArray();
        $termSeries = $months->map(fn($m) => (int) ($terminations[$m->format('Y-m')] ?? 0))->values()->toArray();
        $hireSeries = $months->map(fn($m) => (int) ($hires[$m->format('Y-m')] ?? 0))->values()->toArray();

        // By department (this year)
        $byDept = HrEmployee::where('company_id', $companyId)
            ->whereNotNull('termination_date')
            ->whereYear('termination_date', $this->year)
            ->with('department')
            ->get()
            ->groupBy(fn($e) => $e->department?->name ?? 'Sin departamento')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        $activeCount = HrEmployee::where('company_id', $companyId)->where('status', 'active')->count();
        $totalTermYear = array_sum($termSeries);
        $rate = $activeCount > 0 ? round(($totalTermYear / $activeCount) * 100, 1) : 0;

        return compact('labels', 'termSeries', 'hireSeries', 'byDept', 'rate', 'totalTermYear');
    }

    // ── 3. Ausentismo por departamento ────────────────────────────────────────

    private function absenteeismData(int $companyId): array
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();
        $workingDays  = $this->countWorkingDays(now()->startOfMonth(), now()->endOfMonth());

        $departments = HrDepartment::where('company_id', $companyId)
            ->where('is_active', true)
            ->withCount(['employees' => fn($q) => $q->where('status', 'active')])
            ->get();

        $rows = $departments->map(function ($dept) use ($companyId, $startOfMonth, $endOfMonth, $workingDays) {
            $employeeCount = $dept->employees_count;
            if ($employeeCount === 0) return null;

            $absences  = HrAttendance::where('company_id', $companyId)
                ->whereHas('employee', fn($q) => $q->where('department_id', $dept->id))
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', 'absent')
                ->count();

            $lates = HrAttendance::where('company_id', $companyId)
                ->whereHas('employee', fn($q) => $q->where('department_id', $dept->id))
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', 'late')
                ->count();

            $expected = $employeeCount * $workingDays;
            $rate     = $expected > 0 ? round(($absences / $expected) * 100, 1) : 0;

            return [
                'name'      => $dept->name,
                'employees' => $employeeCount,
                'absences'  => $absences,
                'lates'     => $lates,
                'expected'  => $expected,
                'rate'      => $rate,
            ];
        })->filter()->sortByDesc('rate')->values();

        $totalAbsences = $rows->sum('absences');
        $totalExpected = $rows->sum('expected');
        $globalRate    = $totalExpected > 0 ? round(($totalAbsences / $totalExpected) * 100, 1) : 0;

        return compact('rows', 'globalRate', 'totalAbsences', 'workingDays');
    }

    private function countWorkingDays(\Carbon\Carbon $from, \Carbon\Carbon $to): int
    {
        $days = 0;
        $current = $from->copy();
        while ($current->lte($to)) {
            if (! $current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }
        return $days;
    }
}
