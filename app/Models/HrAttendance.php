<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAttendance extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_attendances';

    protected $fillable = [
        'company_id', 'employee_id', 'project_id', 'session_id', 'device_id',
        'raw_log_in_id', 'raw_log_out_id', 'location_id',
        'date', 'check_in', 'check_out',
        'worked_hours', 'overtime_hours', 'status', 'notes', 'metadata', 'recorded_by',
        'checkin_latitude', 'checkin_longitude', 'location_valid',
    ];

    protected $casts = [
        'date'              => 'date',
        'worked_hours'      => 'decimal:2',
        'overtime_hours'    => 'decimal:2',
        'metadata'          => 'array',
        'checkin_latitude'  => 'decimal:7',
        'checkin_longitude' => 'decimal:7',
        'location_valid'    => 'boolean',
    ];

    const STATUSES = [
        'present'  => 'Presente',
        'absent'   => 'Falta',
        'late'     => 'Tardanza',
        'half_day' => 'Medio día',
        'holiday'  => 'Día festivo',
        'rest_day' => 'Descanso',
        'leave'    => 'Con permiso',
    ];

    const STATUS_COLORS = [
        'present'  => 'bg-green-100 text-green-700',
        'absent'   => 'bg-red-100 text-red-700',
        'late'     => 'bg-yellow-100 text-yellow-700',
        'half_day' => 'bg-orange-100 text-orange-700',
        'holiday'  => 'bg-blue-100 text-blue-700',
        'rest_day' => 'bg-gray-100 text-gray-500',
        'leave'    => 'bg-purple-100 text-purple-700',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(HrAttendanceSession::class, 'session_id');
    }

    public function rawLogIn(): BelongsTo
    {
        return $this->belongsTo(HrAttendanceLog::class, 'raw_log_in_id');
    }

    public function rawLogOut(): BelongsTo
    {
        return $this->belongsTo(HrAttendanceLog::class, 'raw_log_out_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(HrAttendanceLocation::class, 'location_id');
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
}
