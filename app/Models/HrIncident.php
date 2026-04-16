<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrIncident extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_incidents';

    protected $fillable = [
        'company_id', 'employee_id', 'type', 'incident_date', 'description',
        'severity', 'action_taken', 'resolved', 'resolved_at', 'file_path', 'created_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolved'      => 'boolean',
        'resolved_at'   => 'datetime',
    ];

    const TYPES = [
        'tardanza'           => 'Tardanza',
        'falta_injustificada'=> 'Falta injustificada',
        'comportamiento'     => 'Comportamiento inapropiado',
        'accidente_trabajo'  => 'Accidente de trabajo',
        'incumplimiento'     => 'Incumplimiento de normas',
        'otro'               => 'Otro',
    ];

    const SEVERITIES = [
        'low'      => 'Leve',
        'medium'   => 'Moderada',
        'high'     => 'Grave',
        'critical' => 'Crítica',
    ];

    const SEVERITY_COLORS = [
        'low'      => 'bg-blue-100 text-blue-700',
        'medium'   => 'bg-yellow-100 text-yellow-700',
        'high'     => 'bg-orange-100 text-orange-700',
        'critical' => 'bg-red-100 text-red-700',
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

    public function getSeverityColorAttribute(): string
    {
        return self::SEVERITY_COLORS[$this->severity] ?? 'bg-gray-100 text-gray-600';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
