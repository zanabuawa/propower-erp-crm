<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrPayroll;
use App\Models\HrPayrollItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Nueva Nómina')]
class PayrollForm extends Component
{
    public string $period_type = 'biweekly';
    public string $period_start = '';
    public string $period_end = '';
    public string $notes = '';

    /** @var array<int, array> Preview/editable items keyed by employee_id */
    public array $items = [];

    public bool $calculated = false;

    public function mount(): void
    {
        // Auto-suggest current period
        $this->period_start = now()->startOfMonth()->format('Y-m-d');
        $this->period_end   = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedPeriodType(): void
    {
        $now = now();
        if ($this->period_type === 'biweekly') {
            if ($now->day <= 15) {
                $this->period_start = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->period_end   = $now->copy()->startOfMonth()->addDays(14)->format('Y-m-d');
            } else {
                $this->period_start = $now->copy()->startOfMonth()->addDays(15)->format('Y-m-d');
                $this->period_end   = $now->copy()->endOfMonth()->format('Y-m-d');
            }
        } elseif ($this->period_type === 'weekly') {
            $this->period_start = $now->copy()->startOfWeek()->format('Y-m-d');
            $this->period_end   = $now->copy()->endOfWeek()->format('Y-m-d');
        } else {
            $this->period_start = $now->copy()->startOfMonth()->format('Y-m-d');
            $this->period_end   = $now->copy()->endOfMonth()->format('Y-m-d');
        }
        $this->calculated = false;
        $this->items = [];
    }

    public function calculate(): void
    {
        $this->validate([
            'period_type'  => 'required|in:weekly,biweekly,monthly',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        $start = \Carbon\Carbon::parse($this->period_start);
        $end   = \Carbon\Carbon::parse($this->period_end);
        $periodDays = $start->diffInDays($end) + 1;

        $employees = HrEmployee::where('status', 'active')
            ->with('activeContract')
            ->get();

        $this->items = [];

        foreach ($employees as $emp) {
            $dailySalary = $emp->daily_salary;

            // Simple ISPT estimado (tabla 2024 simplificada)
            $monthlyGross = $dailySalary * 30;
            $ispt = $this->estimateISPT($monthlyGross, $periodDays / 30);

            // IMSS cuota obrera estimada (~2.375% del SDI * días)
            $sdi = (float) ($emp->daily_salary_imss ?? $dailySalary * 1.0493);
            $imssEmployee = round($sdi * $periodDays * 0.02375, 2);
            $employerImss = round($sdi * $periodDays * 0.0778, 2);

            $baseSalary  = round($dailySalary * $periodDays, 2);
            $grossSalary = $baseSalary;
            $totalDeductions = $ispt + $imssEmployee;
            $netSalary = $grossSalary - $totalDeductions;

            $this->items[$emp->id] = [
                'employee_id'    => $emp->id,
                'full_name'      => $emp->full_name,
                'department'     => $emp->department?->name ?? '—',
                'position'       => $emp->position?->name ?? '—',
                'days_worked'    => $periodDays,
                'daily_salary'   => round($dailySalary, 2),
                'base_salary'    => $baseSalary,
                'overtime_hours' => 0,
                'overtime_amount'=> 0,
                'food_voucher'   => 0,
                'gross_salary'   => $grossSalary,
                'ispt'           => $ispt,
                'imss_employee'  => $imssEmployee,
                'infonavit_payment'=> 0,
                'loan_payment'   => 0,
                'total_deductions'=> $totalDeductions,
                'employer_imss'  => $employerImss,
                'net_salary'     => $netSalary,
            ];
        }

        $this->calculated = true;
    }

    public function updateItemField(int $empId, string $field, $value): void
    {
        if (!isset($this->items[$empId])) return;

        $this->items[$empId][$field] = (float) $value;

        // Recalculate
        $item = &$this->items[$empId];
        $item['base_salary']    = round($item['daily_salary'] * $item['days_worked'], 2);
        $item['gross_salary']   = round($item['base_salary'] + $item['overtime_amount'] + $item['food_voucher'], 2);
        $item['total_deductions'] = round($item['ispt'] + $item['imss_employee'] + $item['infonavit_payment'] + $item['loan_payment'], 2);
        $item['net_salary']     = round($item['gross_salary'] - $item['total_deductions'], 2);
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
            $payroll = HrPayroll::create([
                'company_id'    => auth()->user()->company_id,
                'folio'         => 'NOM-' . now()->format('Ymd-His'),
                'period_type'   => $this->period_type,
                'period_start'  => $this->period_start,
                'period_end'    => $this->period_end,
                'status'        => 'calculated',
                'notes'         => $this->notes ?: null,
                'created_by'    => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                HrPayrollItem::create([
                    'payroll_id'       => $payroll->id,
                    'employee_id'      => $item['employee_id'],
                    'days_worked'      => $item['days_worked'],
                    'daily_salary'     => $item['daily_salary'],
                    'base_salary'      => $item['base_salary'],
                    'overtime_hours'   => $item['overtime_hours'],
                    'overtime_amount'  => $item['overtime_amount'],
                    'food_voucher'     => $item['food_voucher'],
                    'gross_salary'     => $item['gross_salary'],
                    'ispt'             => $item['ispt'],
                    'imss_employee'    => $item['imss_employee'],
                    'infonavit_payment'=> $item['infonavit_payment'],
                    'loan_payment'     => $item['loan_payment'],
                    'total_deductions' => $item['total_deductions'],
                    'employer_imss'    => $item['employer_imss'],
                    'net_salary'       => $item['net_salary'],
                    'status'           => 'pending',
                ]);
            }

            $payroll->recalculate();

            session()->flash('success', 'Nómina calculada y guardada.');
            $this->redirect(route('hr.payrolls.show', $payroll), navigate: true);
        });
    }

    /** Estimación simple de ISPT mensual (tabla 2024 ISR) */
    private function estimateISPT(float $monthlyGross, float $periodFraction = 1.0): float
    {
        // Tabla anual simplificada → mensual
        $tabla = [
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
        foreach ($tabla as [$li, $ls, $cuotaFija, $pct]) {
            if ($monthlyGross >= $li && $monthlyGross <= $ls) {
                $ispt = $cuotaFija + ($monthlyGross - $li) * ($pct / 100);
                break;
            }
        }

        // Subsidio al empleo simplificado
        $subsidio = $this->subsidioEmpleo($monthlyGross);
        $isptNeto = max(0, $ispt - $subsidio);

        return round($isptNeto * $periodFraction, 2);
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

        return view('livewire.hr.payroll-form', compact('totals'));
    }
}
