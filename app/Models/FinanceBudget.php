<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceBudget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'project_id', 'name',
        'period_type', 'year', 'period_number', 'category',
        'amount_planned', 'amount_actual', 'currency', 'status', 'notes',
    ];

    protected $casts = [
        'amount_planned' => 'decimal:2',
        'amount_actual'  => 'decimal:2',
        'year'           => 'integer',
        'period_number'  => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function cashflows(): HasMany
    {
        return $this->hasMany(FinanceCashflow::class, 'budget_id');
    }

    public function getVarianceAttribute(): float
    {
        return $this->amount_planned - $this->amount_actual;
    }

    public function getExecutionPercentAttribute(): float
    {
        if ($this->amount_planned == 0) return 0;
        return round(($this->amount_actual / $this->amount_planned) * 100, 2);
    }
}
