<?php

namespace App\Livewire\HR;

use App\Models\HrPayroll;
use App\Services\FacturapiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detalle de Nómina')]
class PayrollShow extends Component
{
    public HrPayroll $payroll;

    public bool $confirmApprove = false;
    public bool $confirmPay = false;

    public function mount(HrPayroll $payroll): void
    {
        $this->payroll = $payroll->load(['items.employee', 'createdBy', 'approvedBy']);
    }

    public function approve(): void
    {
        $this->authorize('edit hr');

        if ($this->payroll->status !== 'calculated') {
            session()->flash('error', 'Solo se pueden aprobar nóminas en estado "Calculada".');
            return;
        }

        $this->payroll->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->confirmApprove = false;
        session()->flash('success', 'Nómina aprobada.');
        $this->payroll->refresh();
    }

    public function markPaid(): void
    {
        $this->authorize('edit hr');

        if ($this->payroll->status !== 'approved') {
            session()->flash('error', 'La nómina debe estar aprobada para marcarla como pagada.');
            return;
        }

        $this->payroll->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        $this->confirmPay = false;
        session()->flash('success', 'Nómina marcada como pagada.');
        $this->payroll->refresh();
    }

    public function stampWithFacturapi(): void
    {
        $this->authorize('stamp invoices');

        if (!in_array($this->payroll->status, ['approved', 'paid'])) {
            session()->flash('error', 'La nómina debe estar aprobada o pagada para timbrarla.');
            return;
        }

        $service = app(FacturapiService::class);
        $errors  = 0;
        $stamped = 0;

        foreach ($this->payroll->items as $item) {
            if ($item->status === 'stamped') continue;

            try {
                $employee = $item->employee;

                $payload = [
                    'type'     => 'N',  // Nómina
                    'customer' => [
                        'legal_name' => $employee->full_name,
                        'tax_id'     => $employee->rfc,
                        'tax_system' => '605', // Sueldos y Salarios
                        'zip'        => $employee->postal_code ?? '06600',
                    ],
                    'payment_form'   => '99', // Por definir
                    'items' => [
                        [
                            'quantity'      => 1,
                            'product'       => [
                                'description'  => 'Pago de nómina',
                                'product_key'  => '84111505',
                                'unit_key'     => 'ACT',
                                'price'        => $item->gross_salary,
                                'tax_included' => true,
                                'taxes'        => [],
                            ],
                        ],
                    ],
                    'payroll' => [
                        'type'               => 'O', // Ordinario
                        'payment_date'       => $this->payroll->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                        'initial_payment_date'  => $this->payroll->period_start->format('Y-m-d'),
                        'final_payment_date'    => $this->payroll->period_end->format('Y-m-d'),
                        'days_paid'          => (int) $item->days_worked,
                        'perceptions' => [
                            ['perception_type' => '001', 'code' => '001', 'description' => 'Salario base', 'taxed_amount' => $item->base_salary, 'exempt_amount' => 0],
                        ],
                        'deductions' => array_filter([
                            $item->ispt > 0 ? ['deduction_type' => '001', 'code' => '001', 'description' => 'ISR', 'amount' => $item->ispt] : null,
                            $item->imss_employee > 0 ? ['deduction_type' => '002', 'code' => '002', 'description' => 'Cuota IMSS', 'amount' => $item->imss_employee] : null,
                        ]),
                        'employer_ssnumber'  => $employee->nss ?? '',
                        'employee' => [
                            'employee_number'    => $employee->employee_number ?? (string) $employee->id,
                            'full_name'          => $employee->full_name,
                            'ss_number'          => $employee->nss ?? '',
                            'curp'               => $employee->curp ?? '',
                            'risk_type'          => '1',
                            'periodicity_type'   => match ($this->payroll->period_type) { 'weekly' => '02', 'biweekly' => '04', default => '05' },
                            'contract_type'      => '01',
                            'base_salary'        => $item->daily_salary,
                            'daily_salary'       => $item->daily_salary,
                            'federal_entity_key' => 'AGS',
                            'work_day_type'      => '01',
                            'department'         => $employee->department?->name ?? 'General',
                            'position'           => $employee->position?->name ?? 'Empleado',
                        ],
                    ],
                ];

                $response = $service->createInvoice($payload);

                $item->update([
                    'status'      => 'stamped',
                    'facturapi_id'=> $response->id,
                    'cfdi_uuid'   => $response->uuid ?? null,
                ]);

                $stamped++;
            } catch (\Exception $e) {
                $item->update([
                    'status'      => 'error',
                    'stamp_error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        if ($stamped > 0) {
            $this->payroll->update(['status' => 'stamped']);
        }

        session()->flash(
            $errors > 0 ? 'warning' : 'success',
            "Timbrado: {$stamped} CFDI generados" . ($errors > 0 ? ", {$errors} con error." : '.')
        );

        $this->payroll->refresh()->load('items.employee');
    }

    public function render()
    {
        return view('livewire.hr.payroll-show');
    }
}
