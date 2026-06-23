<?php

namespace App\Livewire\HR;

use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\HrEmployeeBonus;
use App\Models\HrEmployeeLoan;
use App\Models\HrPayroll;
use App\Models\HrPayrollItem;
use App\Services\HrPayrollCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Nueva Nómina')]
class PayrollForm extends Component
{
    public string $period_type  = 'weekly';
    public string $period_start = '';
    public string $period_end   = '';
    public string $notes        = '';
    public string $employee_id  = '';

    /** @var array<int, array> Items editables por employee_id */
    public array $items = [];

    public bool $calculated = false;

    public function mount(): void
    {
        $now = now();
        $this->period_start = $now->copy()->startOfWeek()->format('Y-m-d');
        $this->period_end   = $now->copy()->endOfWeek()->format('Y-m-d');
    }

    public function updatedPeriodType(): void
    {
        $now = now();
        if ($this->period_type === 'weekly') {
            $this->period_start = $now->copy()->startOfWeek()->format('Y-m-d');
            $this->period_end   = $now->copy()->endOfWeek()->format('Y-m-d');
        } elseif ($this->period_type === 'biweekly') {
            if ($now->day <= 15) {
                $this->period_start = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->period_end   = $now->copy()->startOfMonth()->addDays(14)->format('Y-m-d');
            } else {
                $this->period_start = $now->copy()->startOfMonth()->addDays(15)->format('Y-m-d');
                $this->period_end   = $now->copy()->endOfMonth()->format('Y-m-d');
            }
        } else {
            $this->period_start = $now->copy()->startOfMonth()->format('Y-m-d');
            $this->period_end   = $now->copy()->endOfMonth()->format('Y-m-d');
        }
        $this->calculated = false;
        $this->items = [];
    }

    public function updatedEmployeeId(): void
    {
        $this->calculated = false;
        $this->items = [];
    }

    public function calculate(): void
    {
        $this->validate([
            'period_type' => 'required|in:weekly,biweekly,monthly',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'employee_id' => 'nullable|exists:hr_employees,id',
        ]);

        $companyId = auth()->user()->company_id;
        $employeeId = $this->employee_id !== '' ? (int) $this->employee_id : null;

        $this->items = app(HrPayrollCalculator::class)->calculate(
            $companyId,
            $this->period_start,
            $this->period_end,
            $employeeId,
        );

        $this->calculated = true;
    }

    private function resolveDailySalary(HrEmployee $emp): float
    {
        if ($emp->activeContract) {
            $salary = (float) $emp->activeContract->salary;
            return match ($emp->activeContract->salary_period ?? 'monthly') {
                'daily'    => $salary,
                'weekly'   => $salary / 7,
                'biweekly' => $salary / 15,
                default    => $salary / 30,
            };
        }
        return (float) ($emp->daily_salary_imss ?: $emp->daily_salary);
    }

    public function updateItemField(int $empId, string $field, $value): void
    {
        if (! isset($this->items[$empId])) return;

        $this->items[$empId][$field] = (float) $value;
        $item = &$this->items[$empId];

        if ($field === 'days_worked') {
            $item['worked_hours'] = round($item['days_worked'] * 8, 2);
        }

        $workedHours = max(0, (float) ($item['worked_hours'] ?? ($item['days_worked'] * 8)));
        $overtimeHours = max(0, (float) ($item['overtime_hours'] ?? 0));
        $hourlySalary = (float) $item['daily_salary'] / 8;
        $regularHours = max(0, $workedHours - $overtimeHours);

        $item['worked_hours'] = round($workedHours, 2);
        $item['regular_hours'] = round($regularHours, 2);
        $item['days_worked'] = round($workedHours / 8, 2);
        $item['base_salary'] = round($hourlySalary * $regularHours, 2);
        $item['overtime_amount'] = round($overtimeHours * $hourlySalary * 2, 2);
        $item['gross_salary']    = round(
            $item['base_salary'] + $item['overtime_amount'] + $item['bonus_amount'] + $item['food_voucher'],
            2
        );
        $item['total_deductions'] = round(
            $item['ispt'] + $item['imss_employee'] + $item['infonavit_payment'] + $item['loan_payment'],
            2
        );
        $item['net_salary'] = max(0, round($item['gross_salary'] - $item['total_deductions'], 2));

        if ($field === 'bonus_amount') {
            $item['other_perceptions'] = $item['bonus_amount'] > 0
                ? [['concept' => 'Complemento manual', 'amount' => $item['bonus_amount'], 'source' => 'manual']]
                : [];
            $item['bonus_ids'] = [];
        }

        if ($field === 'loan_payment') {
            $item['other_deductions'] = $item['loan_payment'] > 0
                ? [['concept' => 'Descuento manual', 'amount' => $item['loan_payment'], 'source' => 'manual']]
                : [];
            $item['loan_ids'] = [];
        }
    }

    public function save(): void
    {
        $this->validate([
            'period_type'  => 'required',
            'period_start' => 'required|date',
            'period_end'   => 'required|date',
        ]);

        if (empty($this->items)) {
            $this->addError('items', 'Debes calcular la nómina primero.');
            return;
        }

        DB::transaction(function () {
            $companyId = auth()->user()->company_id;

            $payroll = HrPayroll::create([
                'company_id'   => $companyId,
                'folio'        => 'NOM-' . now()->format('Ymd-His'),
                'period_type'  => $this->period_type,
                'period_start' => $this->period_start,
                'period_end'   => $this->period_end,
                'status'       => 'calculated',
                'notes'        => $this->notes ?: null,
                'created_by'   => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $savedItem = HrPayrollItem::create([
                    'payroll_id'        => $payroll->id,
                    'employee_id'       => $item['employee_id'],
                    'days_worked'       => $item['days_worked'],
                    'daily_salary'      => $item['daily_salary'],
                    'base_salary'       => $item['base_salary'],
                    'overtime_hours'    => $item['overtime_hours'],
                    'overtime_amount'   => $item['overtime_amount'],
                    'food_voucher'      => $item['food_voucher'],
                    'gross_salary'      => $item['gross_salary'],
                    'ispt'              => $item['ispt'],
                    'imss_employee'     => $item['imss_employee'],
                    'infonavit_payment' => $item['infonavit_payment'],
                    'loan_payment'      => $item['loan_payment'],
                    'total_deductions'  => $item['total_deductions'],
                    'employer_imss'     => $item['employer_imss'],
                    'net_salary'        => $item['net_salary'],
                    'status'            => 'pending',
                    'other_perceptions' => $item['other_perceptions'] ?? null,
                    'other_deductions'  => $item['other_deductions'] ?? null,
                ]);

                // Marcar bonos como aplicados
                if ($item['bonus_amount'] > 0) {
                    HrEmployeeBonus::whereIn('id', $item['bonus_ids'] ?? [])
                        ->where('employee_id', $item['employee_id'])
                        ->where('is_applied', false)
                        ->update(['is_applied' => true, 'payroll_item_id' => $savedItem->id]);
                }

                // Descontar cuota de préstamos
                if ($item['loan_payment'] > 0) {
                    $remaining = (float) $item['loan_payment'];
                    foreach (HrEmployeeLoan::where('employee_id', $item['employee_id'])
                        ->where('status', 'active')->orderBy('loan_date')->get() as $loan) {
                        if ($remaining <= 0) break;
                        $deduct = min($remaining, (float) $loan->balance);
                        $loan->registerPayment($deduct);
                        $remaining -= $deduct;
                    }
                }
            }

            $payroll->recalculate();
            session()->flash('success', 'Nómina guardada correctamente.');
            $this->redirect(route('hr.payrolls.show', $payroll), navigate: true);
        });
    }

    private function estimateISPT(float $monthlyGross, float $periodFraction = 1.0): float
    {
        $tabla = [
            [0.01,       746.04,      0,          1.92],
            [746.05,     6332.05,     14.32,      6.40],
            [6332.06,    11128.01,    371.83,     10.88],
            [11128.02,   12935.82,    893.63,     16.00],
            [12935.83,   15487.71,    1182.88,    17.92],
            [15487.72,   31236.49,    1640.18,    21.36],
            [31236.50,   49233.00,    5004.12,    23.52],
            [49233.01,   93993.90,    9236.89,    30.00],
            [93993.91,   125325.20,   22665.17,   32.00],
            [125325.21,  375975.61,   32691.18,   34.00],
            [375975.62,  PHP_INT_MAX, 117912.32,  35.00],
        ];

        $ispt = 0;
        foreach ($tabla as [$li, $ls, $cuotaFija, $pct]) {
            if ($monthlyGross >= $li && $monthlyGross <= $ls) {
                $ispt = $cuotaFija + ($monthlyGross - $li) * ($pct / 100);
                break;
            }
        }

        $subsidio = $this->subsidioEmpleo($monthlyGross);
        return round(max(0, $ispt - $subsidio) * $periodFraction, 2);
    }

    private function subsidioEmpleo(float $monthly): float
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

    public function render()
    {
        $totals = [
            'gross'      => collect($this->items)->sum('gross_salary'),
            'deductions' => collect($this->items)->sum('total_deductions'),
            'net'        => collect($this->items)->sum('net_salary'),
            'employees'  => count($this->items),
        ];

        $employeeOptions = HrEmployee::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'employee_number', 'first_name', 'last_name', 'second_last_name']);

        return view('livewire.hr.payroll-form', compact('totals', 'employeeOptions'));
    }
}

