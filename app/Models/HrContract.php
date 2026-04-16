<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrContract extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_contracts';

    protected $fillable = [
        'company_id', 'employee_id', 'contract_number', 'type',
        'start_date', 'end_date', 'salary', 'salary_period',
        'work_shift', 'work_hours_per_week', 'benefits',
        'status', 'file_path', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'salary'             => 'decimal:2',
        'benefits'           => 'array',
        'work_hours_per_week'=> 'integer',
    ];

    const TYPES = [
        'indefinido'           => 'Tiempo indefinido',
        'temporal'             => 'Temporal',
        'honorarios'           => 'Honorarios',
        'obra_determinada'     => 'Obra determinada',
        'capacitacion_inicial' => 'Capacitación inicial',
    ];

    const STATUSES = [
        'draft'      => 'Borrador',
        'active'     => 'Vigente',
        'expired'    => 'Vencido',
        'terminated' => 'Rescindido',
    ];

    const STATUS_COLORS = [
        'draft'      => 'bg-gray-100 text-gray-600',
        'active'     => 'bg-green-100 text-green-700',
        'expired'    => 'bg-yellow-100 text-yellow-700',
        'terminated' => 'bg-red-100 text-red-700',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
