<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeBonus extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_employee_bonuses';

    protected $fillable = [
        'company_id', 'employee_id', 'concept_id', 'amount',
        'reason', 'apply_at', 'is_applied', 'payroll_item_id'
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'apply_at'   => 'date',
        'is_applied' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(HrPayrollConcept::class, 'concept_id');
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(HrPayrollItem::class, 'payroll_item_id');
    }
}
