<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrLeave extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_leaves';

    protected $fillable = [
        'company_id', 'employee_id', 'type', 'start_date', 'end_date',
        'business_days', 'reason', 'status', 'approved_by', 'approved_at',
        'rejection_reason', 'imss_certificate_number', 'file_path', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
    ];

    const TYPES = [
        'vacaciones'          => 'Vacaciones',
        'incapacidad_imss'    => 'Incapacidad IMSS',
        'incapacidad_laboral' => 'Incapacidad laboral',
        'permiso_con_goce'    => 'Permiso con goce de sueldo',
        'permiso_sin_goce'    => 'Permiso sin goce de sueldo',
        'maternidad'          => 'Licencia de maternidad',
        'paternidad'          => 'Licencia de paternidad',
        'duelo'               => 'Licencia por duelo',
    ];

    const STATUSES = [
        'pending'   => 'Pendiente',
        'approved'  => 'Aprobada',
        'rejected'  => 'Rechazada',
        'cancelled' => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'pending'   => 'bg-yellow-100 text-yellow-700',
        'approved'  => 'bg-green-100 text-green-700',
        'rejected'  => 'bg-red-100 text-red-700',
        'cancelled' => 'bg-gray-100 text-gray-500',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
