<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrDepartment extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_departments';

    protected $fillable = [
        'company_id', 'parent_id', 'name', 'code', 'description', 'manager_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(HrDepartment::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'manager_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(HrPosition::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HrEmployee::class, 'department_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getActiveEmployeesCountAttribute(): int
    {
        return $this->employees()->where('status', 'active')->count();
    }
}
