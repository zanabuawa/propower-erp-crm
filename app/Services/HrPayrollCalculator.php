<?php

namespace App\Services;

use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\HrEmployeeBonus;
use App\Models\HrEmployeeLoan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HrPayrollCalculator
{
    public function calculate(int $companyId, string $periodStart, string $periodEnd, ?int $employeeId = null): array
    {
        $start = Carbon::parse($periodStart);
        $end = Carbon::parse($periodEnd);

        $employees = HrEmployee::where('company_id', $companyId)
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->where('status', 'active')
            ->with(['activeContract', 'department', 'position'])
            ->get();

        $employeeIds = $employees->pluck('id');

        $attendances = HrAttendance::where('company_id', $companyId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('status', ['present', 'late', 'half_day', 'leave'])
            ->whereIn('employee_id', $employeeIds)
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as days_worked'),
                DB::raw('SUM(COALESCE(worked_hours, 0)) as total_hours'),
                DB::raw('SUM(COALESCE(overtime_hours, 0)) as overtime_hours')
            )
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $loans = HrEmployeeLoan::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('loan_date')
            ->get()
            ->groupBy('employee_id');

        $bonuses = HrEmployeeBonus::with('concept')
            ->where('company_id', $companyId)
            ->where('is_applied', false)
            ->where('apply_at', '<=', $end->toDateString())
            ->whereIn('employee_id', $employeeIds)
            ->orderBy('apply_at')
            ->get()
            ->groupBy('employee_id');

        $items = [];

        foreach ($employees as $employee) {
            $dailySalary = $this->resolveDailySalary($employee);
            $attendance = $attendances->get($employee->id);
            $attendanceDays = $attendance ? (float) $attendance->days_worked : 0;
            $workedHours = $attendance ? (float) $attendance->total_hours : 0;
            $overtimeHours = $attendance ? (float) $attendance->overtime_hours : 0;
            $regularHours = max(0, $workedHours - $overtimeHours);
            $hourlySalary = $dailySalary / 8;
            $daysWorked = round($workedHours / 8, 2);

            $employeeBonuses = $bonuses->get($employee->id, collect());
            $otherPerceptions = $this->bonusPerceptions($employeeBonuses);
            $bonusAmount = round($otherPerceptions->sum('amount'), 2);

            $employeeLoans = $loans->get($employee->id, collect());
            $loanPayment = $this->loanInstallmentTotal($employeeLoans);
            $loanDeductions = $this->loanDeductions($employeeLoans, $loanPayment);

            $baseSalary = round($hourlySalary * $regularHours, 2);
            $overtimeAmount = round($overtimeHours * $hourlySalary * 2, 2);
            $grossSalary = round($baseSalary + $overtimeAmount + $bonusAmount, 2);

            $periodDays = max($daysWorked, 1);
            $monthlyGross = $dailySalary * 30;
            $ispt = $this->estimateISPT($monthlyGross, $periodDays / 30);
            $sdi = (float) ($employee->daily_salary_imss ?? $dailySalary * 1.0493);
            $imssEmployee = round($sdi * $periodDays * 0.02375, 2);
            $employerImss = round($sdi * $periodDays * 0.0778, 2);

            $totalDeductions = round($ispt + $imssEmployee + $loanPayment, 2);
            $netSalary = max(0, round($grossSalary - $totalDeductions, 2));

            $items[$employee->id] = [
                'employee_id' => $employee->id,
                'full_name' => $employee->full_name,
                'department' => $employee->department?->name ?? '—',
                'position' => $employee->position?->name ?? '—',
                'days_worked' => $daysWorked,
                'worked_hours' => round($workedHours, 2),
                'regular_hours' => round($regularHours, 2),
                'attendance_days' => $attendanceDays,
                'overtime_hours' => $overtimeHours,
                'daily_salary' => round($dailySalary, 2),
                'base_salary' => $baseSalary,
                'overtime_amount' => $overtimeAmount,
                'bonus_amount' => $bonusAmount,
                'food_voucher' => 0,
                'gross_salary' => $grossSalary,
                'ispt' => $ispt,
                'imss_employee' => $imssEmployee,
                'infonavit_payment' => 0,
                'loan_payment' => $loanPayment,
                'total_deductions' => $totalDeductions,
                'employer_imss' => $employerImss,
                'net_salary' => $netSalary,
                'from_checador' => $attendance !== null,
                'bonus_ids' => $employeeBonuses->pluck('id')->values()->all(),
                'loan_ids' => $employeeLoans->pluck('id')->values()->all(),
                'other_perceptions' => $otherPerceptions->values()->all(),
                'other_deductions' => $loanDeductions->values()->all(),
            ];
        }

        return $items;
    }

    private function resolveDailySalary(HrEmployee $employee): float
    {
        if ($employee->activeContract) {
            $salary = (float) $employee->activeContract->salary;

            return match ($employee->activeContract->salary_period ?? 'monthly') {
                'daily' => $salary,
                'weekly' => $salary / 7,
                'biweekly' => $salary / 15,
                default => $salary / 30,
            };
        }

        return (float) ($employee->daily_salary_imss ?: $employee->daily_salary);
    }

    private function bonusPerceptions(Collection $bonuses): Collection
    {
        return $bonuses
            ->map(fn (HrEmployeeBonus $bonus) => [
                'concept_id' => $bonus->concept_id,
                'concept' => $bonus->concept?->name ?? $bonus->reason ?? 'Bono',
                'amount' => (float) $bonus->amount,
                'source' => 'bonus',
                'source_id' => $bonus->id,
            ])
            ->filter(fn (array $row) => $row['amount'] > 0);
    }

    private function loanInstallmentTotal(Collection $loans): float
    {
        return round($loans->sum(fn (HrEmployeeLoan $loan) => min((float) $loan->installment_amount, (float) $loan->balance)), 2);
    }

    private function loanDeductions(Collection $loans, float $total): Collection
    {
        $remaining = $total;

        return $loans
            ->map(function (HrEmployeeLoan $loan) use (&$remaining) {
                if ($remaining <= 0) {
                    return null;
                }

                $amount = min($remaining, (float) $loan->balance);
                $remaining -= $amount;

                return [
                    'concept' => $loan->reason ? 'Prestamo: '.$loan->reason : 'Prestamo empresa',
                    'amount' => round($amount, 2),
                    'source' => 'loan',
                    'source_id' => $loan->id,
                ];
            })
            ->filter();
    }

    private function estimateISPT(float $monthlyGross, float $periodFraction = 1.0): float
    {
        $table = [
            [0.01, 746.04, 0, 1.92],
            [746.05, 6332.05, 14.32, 6.40],
            [6332.06, 11128.01, 371.83, 10.88],
            [11128.02, 12935.82, 893.63, 16.00],
            [12935.83, 15487.71, 1182.88, 17.92],
            [15487.72, 31236.49, 1640.18, 21.36],
            [31236.50, 49233.00, 5004.12, 23.52],
            [49233.01, 93993.90, 9236.89, 30.00],
            [93993.91, 125325.20, 22665.17, 32.00],
            [125325.21, 375975.61, 32691.18, 34.00],
            [375975.62, PHP_INT_MAX, 117912.32, 35.00],
        ];

        $ispt = 0;

        foreach ($table as [$lowerLimit, $upperLimit, $fixedFee, $percentage]) {
            if ($monthlyGross >= $lowerLimit && $monthlyGross <= $upperLimit) {
                $ispt = $fixedFee + ($monthlyGross - $lowerLimit) * ($percentage / 100);
                break;
            }
        }

        return round(max(0, $ispt - $this->employmentSubsidy($monthlyGross)) * $periodFraction, 2);
    }

    private function employmentSubsidy(float $monthly): float
    {
        if ($monthly <= 1768.96) return 407.02;
        if ($monthly <= 1978.70) return 406.83;
        if ($monthly <= 2653.38) return 406.62;
        if ($monthly <= 3472.84) return 392.77;
        if ($monthly <= 3537.87) return 382.46;
        if ($monthly <= 4446.15) return 354.23;
        if ($monthly <= 4717.18) return 324.87;
        if ($monthly <= 5335.42) return 294.63;
        if ($monthly <= 6224.67) return 253.54;
        if ($monthly <= 7113.90) return 217.61;

        return 0;
    }
}
