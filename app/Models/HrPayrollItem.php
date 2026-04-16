<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrPayrollItem extends Model
{
    protected $table = 'hr_payroll_items';

    protected $fillable = [
        'payroll_id', 'employee_id',
        'days_worked', 'daily_salary', 'base_salary',
        'overtime_hours', 'overtime_amount', 'sunday_premium', 'holiday_worked',
        'vacation_days_paid', 'vacation_premium', 'christmas_bonus', 'food_voucher',
        'other_perceptions', 'gross_salary',
        'ispt', 'imss_employee', 'infonavit_payment', 'loan_payment',
        'other_deductions', 'total_deductions',
        'employer_imss', 'employer_infonavit', 'employer_retirement',
        'net_salary', 'status',
        'facturapi_id', 'cfdi_uuid', 'cfdi_xml_path', 'cfdi_pdf_path', 'stamp_error',
    ];

    protected $casts = [
        'days_worked'         => 'decimal:2',
        'daily_salary'        => 'decimal:2',
        'base_salary'         => 'decimal:2',
        'overtime_hours'      => 'decimal:2',
        'overtime_amount'     => 'decimal:2',
        'sunday_premium'      => 'decimal:2',
        'holiday_worked'      => 'decimal:2',
        'vacation_days_paid'  => 'decimal:2',
        'vacation_premium'    => 'decimal:2',
        'christmas_bonus'     => 'decimal:2',
        'food_voucher'        => 'decimal:2',
        'other_perceptions'   => 'array',
        'gross_salary'        => 'decimal:2',
        'ispt'                => 'decimal:2',
        'imss_employee'       => 'decimal:2',
        'infonavit_payment'   => 'decimal:2',
        'loan_payment'        => 'decimal:2',
        'other_deductions'    => 'array',
        'total_deductions'    => 'decimal:2',
        'employer_imss'       => 'decimal:2',
        'employer_infonavit'  => 'decimal:2',
        'employer_retirement' => 'decimal:2',
        'net_salary'          => 'decimal:2',
    ];

    const STATUSES = [
        'pending' => 'Pendiente',
        'stamped' => 'Timbrado',
        'error'   => 'Error',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(HrPayroll::class, 'payroll_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /** Calculate all fields from base data */
    public function calculate(): void
    {
        $this->base_salary    = round($this->days_worked * $this->daily_salary, 2);
        $this->gross_salary   = round(
            $this->base_salary + $this->overtime_amount + $this->sunday_premium +
            $this->holiday_worked + $this->vacation_premium + $this->christmas_bonus +
            $this->food_voucher + collect($this->other_perceptions ?? [])->sum('amount'),
            2
        );
        $this->total_deductions = round(
            $this->ispt + $this->imss_employee + $this->infonavit_payment +
            $this->loan_payment + collect($this->other_deductions ?? [])->sum('amount'),
            2
        );
        $this->net_salary = round($this->gross_salary - $this->total_deductions, 2);
    }
}
