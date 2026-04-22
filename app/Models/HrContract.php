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
        'entry_time', 'exit_time', 'work_days', 'saturday_hours', 'tolerance_minutes',
        'status', 'file_path', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'salary'             => 'decimal:2',
        'benefits'           => 'array',
        'work_hours_per_week'=> 'integer',
        'work_days'          => 'array',
        'saturday_hours'     => 'decimal:2',
        'tolerance_minutes'  => 'integer',
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

    // ── Schedule helpers ───────────────────────────────────────────────────────

    /**
     * Returns the expected work hours for a given date (0 = rest day).
     */
    public function expectedHoursOn(\Carbon\Carbon $date): float
    {
        $isoDay   = (int) $date->isoFormat('E'); // 1=Mon … 7=Sun
        $workDays = $this->work_days ?? [1, 2, 3, 4, 5];

        if (! in_array($isoDay, $workDays, true)) {
            return 0;
        }

        if ($isoDay === 6) { // Saturday
            return (float) ($this->saturday_hours ?? 0);
        }

        if ($this->entry_time && $this->exit_time) {
            $entry = \Carbon\Carbon::createFromTimeString($this->entry_time);
            $exit  = \Carbon\Carbon::createFromTimeString($this->exit_time);
            return round($entry->diffInMinutes($exit) / 60, 2);
        }

        return 8.0;
    }

    /**
     * Returns true if the given date is a scheduled workday.
     */
    public function isWorkDay(\Carbon\Carbon $date): bool
    {
        return $this->expectedHoursOn($date) > 0;
    }

    /**
     * Returns true if the given HH:MM string is past entry_time + tolerance.
     */
    public function isLate(string $timeHis): bool
    {
        if (! $this->entry_time) {
            return false;
        }

        $deadline = \Carbon\Carbon::createFromTimeString($this->entry_time)
            ->addMinutes($this->tolerance_minutes ?? 10);
        $checkin  = \Carbon\Carbon::createFromFormat('H:i:s', $timeHis);

        return $checkin->gt($deadline);
    }

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
