<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrAttendance;
use App\Models\HrPerformanceEvaluation;
use App\Models\HrPayrollItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Indicadores de Capital Humano')]
class HrAnalytics extends Component
{
    public ?int $branchId = null;
    public int $year;

    public function mount()
    {
        $this->year = now()->year;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Base query for employees
        $empBase = HrEmployee::where('company_id', $companyId)
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId));

        // 1. Headcount
        $activeCount = (clone $empBase)->where('status', 'active')->count();
        $inactiveCount = (clone $empBase)->where('status', 'inactive')->count();
        $terminatedThisYear = (clone $empBase)
            ->whereYear('termination_date', $this->year)
            ->count();

        // 2. Turnover Rate (Simplificada: Bajas / Promedio de Activos)
        $turnoverRate = 0;
        if ($activeCount > 0) {
            $turnoverRate = ($terminatedThisYear / $activeCount) * 100;
        }

        // 3. Seniority
        $avgSeniority = (clone $empBase)->where('status', 'active')->get()->avg('antiquity_years') ?? 0;

        // 4. Costs (Current active payroll cost)
        $monthlyCost = (clone $empBase)->where('status', 'active')->sum('salary');
        
        $costsByDept = HrDepartment::where('company_id', $companyId)
            ->withSum(['employees' => function($q) {
                $q->where('status', 'active');
            }], 'salary')
            ->get()
            ->map(fn($d) => [
                'label' => $d->name,
                'value' => (float) $d->employees_sum_salary
            ])
            ->filter(fn($d) => $d['value'] > 0);

        // 5. Attendance (Last 30 days)
        $attendanceStats = HrAttendance::where('company_id', $companyId)
            ->where('date', '>=', now()->subDays(30))
            ->when($this->branchId, fn($q) => $q->whereHas('employee', fn($e) => $e->where('branch_id', $this->branchId)))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $totalAttRecords = $attendanceStats->sum();
        $punctualityRate = $totalAttRecords > 0
            ? round(($attendanceStats->get('present', 0) / $totalAttRecords) * 100, 1)
            : null;
        $absenteeismRate = $totalAttRecords > 0
            ? round(($attendanceStats->get('absent', 0) / $totalAttRecords) * 100, 1)
            : null;

        // 6. Performance
        $avgPerformance = HrPerformanceEvaluation::where('company_id', $companyId)
            ->whereYear('evaluation_date', $this->year)
            ->avg('overall_score') ?? 0;

        // 7. Distribution by gender
        $genderDist = (clone $empBase)->where('status', 'active')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->pluck('count', 'gender');

        // 8. Top 10 employees by salary cost
        $topByCost = (clone $empBase)->where('status', 'active')
            ->with(['position', 'department'])
            ->orderByDesc('salary')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'salary', 'salary_period', 'position_id', 'department_id']);

        // 9. Monthly turnover trend (last 12 months)
        $turnoverMonths = collect(range(11, 0))->map(fn($i) => now()->subMonths($i));
        $termByMonth = HrEmployee::where('company_id', $companyId)
            ->whereNotNull('termination_date')
            ->whereDate('termination_date', '>=', now()->subMonths(11)->startOfMonth())
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->selectRaw("DATE_FORMAT(termination_date, '%Y-%m') as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');
        $hiresByMonth = HrEmployee::where('company_id', $companyId)
            ->whereDate('hire_date', '>=', now()->subMonths(11)->startOfMonth())
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->selectRaw("DATE_FORMAT(hire_date, '%Y-%m') as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $turnoverTrend = [
            'labels' => $turnoverMonths->map(fn($m) => $m->translatedFormat('M y'))->values()->toArray(),
            'terms'  => $turnoverMonths->map(fn($m) => (int)($termByMonth[$m->format('Y-m')] ?? 0))->values()->toArray(),
            'hires'  => $turnoverMonths->map(fn($m) => (int)($hiresByMonth[$m->format('Y-m')] ?? 0))->values()->toArray(),
        ];

        $branches = Branch::where('company_id', $companyId)->get();

        return view('livewire.hr.hr-analytics', compact(
            'activeCount', 'inactiveCount', 'terminatedThisYear', 'turnoverRate',
            'avgSeniority', 'monthlyCost', 'costsByDept', 'attendanceStats',
            'punctualityRate', 'absenteeismRate',
            'avgPerformance', 'genderDist', 'branches',
            'topByCost', 'turnoverTrend'
        ));
    }
}
