<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrPosition extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_positions';

    protected $fillable = [
        'company_id', 'department_id', 'name', 'code', 'description',
        'salary_type', 'min_salary', 'max_salary', 'is_active',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    const SALARY_TYPES = [
        'hourly'    => 'Por hora',
        'daily'     => 'Diario',
        'weekly'    => 'Semanal',
        'biweekly'  => 'Quincenal',
        'monthly'   => 'Mensual',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HrEmployee::class, 'position_id');
    }
}
