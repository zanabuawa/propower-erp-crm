<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class HrPayrollConcept extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_payroll_concepts';

    protected $fillable = [
        'company_id', 'name', 'code', 'type', 'is_taxable', 'is_active'
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_active'  => 'boolean',
    ];

    const TYPES = [
        'perception' => 'Percepción',
        'deduction'  => 'Deducción',
    ];
}
