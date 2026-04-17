<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrPayroll extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_payrolls';

    protected $fillable = [
        'company_id', 'folio', 'period_type', 'period_start', 'period_end',
        'status', 'total_employees', 'total_gross', 'total_deductions',
        'total_net', 'total_employer_imss', 'notes', 'description', 
        'labels', 'checklist', 'members',
        'created_by', 'approved_by', 'approved_at', 'paid_at', 'facturapi_batch_id',
    ];

    protected $casts = [
        'period_start'       => 'date',
        'period_end'         => 'date',
        'total_gross'        => 'decimal:2',
        'total_deductions'   => 'decimal:2',
        'total_net'          => 'decimal:2',
        'total_employer_imss'=> 'decimal:2',
        'approved_at'        => 'datetime',
        'paid_at'            => 'datetime',
        'labels'             => 'array',
        'checklist'          => 'array',
        'members'            => 'array',
    ];

    const PERIOD_TYPES = [
        'weekly'    => 'Semanal',
        'biweekly'  => 'Quincenal',
        'monthly'   => 'Mensual',
    ];

    const STATUSES = [
        'draft'      => 'Borrador',
        'calculated' => 'Calculada',
        'approved'   => 'Aprobada',
        'paid'       => 'Pagada',
        'stamped'    => 'Timbrada',
    ];

    const STATUS_COLORS = [
        'draft'      => 'bg-gray-100 text-gray-600',
        'calculated' => 'bg-blue-100 text-blue-700',
        'approved'   => 'bg-indigo-100 text-indigo-700',
        'paid'       => 'bg-green-100 text-green-700',
        'stamped'    => 'bg-emerald-100 text-emerald-700',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HrPayrollItem::class, 'payroll_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('d/m/Y') . ' – ' . $this->period_end->format('d/m/Y');
    }

    /** Recalculate totals from items */
    public function recalculate(): void
    {
        $this->total_employees  = $this->items()->count();
        $this->total_gross      = $this->items()->sum('gross_salary');
        $this->total_deductions = $this->items()->sum('total_deductions');
        $this->total_net        = $this->items()->sum('net_salary');
        $this->total_employer_imss = $this->items()->sum('employer_imss');
        $this->save();
    }
}
