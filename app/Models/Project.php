<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\HrEmployee;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'status',
        'customer_id',
        'sale_order_id',
        'contract_reference',
        'branch_id',
        'responsible_user_id',
        'start_date',
        'end_date',
        'budget',
        'cost_actual',
        'revenue_amount',
        'progress',
        'currency',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'cost_actual' => 'decimal:2',
        'revenue_amount' => 'decimal:2',
        'progress' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function getBudgetVarianceAttribute(): float
    {
        return ($this->budget ?? 0) - ($this->cost_actual ?? 0);
    }

    public function getTotalLaborCostAttribute(): float
    {
        return (float) $this->employees()
            ->wherePivot('is_active', true)
            ->get()
            ->sum(fn($e) => ($e->pivot->hours_assigned ?? 0) * ($e->pivot->cost_per_hour ?? 0));
    }

    public function getTotalMaterialCostAttribute(): float
    {
        return (float) $this->materials()
            ->whereIn('status', ['usado', 'solicitado', 'comprado'])
            ->get()
            ->sum(fn($m) => ($m->quantity_used ?: $m->quantity_needed) * ($m->unit_cost ?? 0));
    }

    public function getTotalExpenseCostAttribute(): float
    {
        return (float) $this->expenses()
            ->where('status', '!=', 'rechazado')
            ->sum('amount');
    }

    public function getTotalConsolidatedCostAttribute(): float
    {
        return $this->total_labor_cost + $this->total_material_cost + $this->total_expense_cost;
    }

    public function getEffectiveRevenueAttribute(): float
    {
        if ($this->revenue_amount > 0) {
            return (float) $this->revenue_amount;
        }
        return (float) ($this->saleOrder?->total ?? 0);
    }

    public function getProfitAmountAttribute(): float
    {
        return $this->effective_revenue - $this->total_consolidated_cost;
    }

    public function getProfitMarginPctAttribute(): ?float
    {
        if ($this->effective_revenue <= 0) return null;
        return ($this->profit_amount / $this->effective_revenue) * 100;
    }

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function budgetVersions(): HasMany
    {
        return $this->hasMany(ProjectBudgetVersion::class)->orderByDesc('version');
    }

    public function activeBudgetVersion(): HasMany
    {
        return $this->hasMany(ProjectBudgetVersion::class)->where('status', 'vigente')->limit(1);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function purchaseRequisitions(): HasMany
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function workPermits(): HasMany
    {
        return $this->hasMany(WorkPermit::class);
    }

    public function workReports(): HasMany
    {
        return $this->hasMany(WorkReport::class);
    }

    public function workPhotoReports(): HasMany
    {
        return $this->hasMany(WorkPhotoReport::class);
    }

    public function workIncidentReports(): HasMany
    {
        return $this->hasMany(WorkIncidentReport::class);
    }

    public function workPrograms(): HasMany
    {
        return $this->hasMany(WorkProgram::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'is_active', 'joined_at', 'left_at', 'notes')
            ->withTimestamps();
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(HrEmployee::class, 'project_employees', 'project_id', 'employee_id')
            ->withPivot('role', 'start_date', 'end_date', 'hours_assigned', 'cost_per_hour', 'notes', 'is_active')
            ->withTimestamps()
            ->orderByPivot('is_active', 'desc');
    }
}
