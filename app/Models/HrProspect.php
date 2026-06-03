<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrProspect extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_prospects';

    protected $fillable = [
        'company_id',
        'position_id',
        'job_opening_id',
        'first_name',
        'last_name',
        'second_last_name',
        'email',
        'phone',
        'source',
        'cv_path',
        'initial_notes',
        'interview_date',
        'interview_type',
        'calendar_color',
        'interviewer_id',
        'scheduled_by_id',
        'employee_id',
        'status',
        'test_type',
        'reminder_24h_sent',
        'reminder_1h_sent',
    ];

    protected $casts = [
        'interview_date' => 'datetime',
        'reminder_24h_sent' => 'boolean',
        'reminder_1h_sent' => 'boolean',
    ];

    const INTERVIEW_TYPES = [
        'presencial' => 'Presencial',
        'virtual'    => 'Virtual',
    ];

    const SOURCES = [
        'linkedin'       => 'LinkedIn',
        'recomendacion'  => 'Recomendación',
        'bolsa_trabajo'  => 'Bolsa de trabajo',
        'redes_sociales' => 'Redes sociales',
        'otro'           => 'Otro',
    ];

    const STATUSES = [
        'nuevo'               => 'Nuevo',
        'evaluando'           => 'Evaluando',
        'entrevista_agendada' => 'Entrevista agendada',
        'entrevistado'        => 'Entrevistado',
        'en_revision'         => 'En revisión',
        'aprobado'            => 'Aprobado',
        'rechazado'           => 'Rechazado',
        'contratado'          => 'Contratado',
    ];

    const STATUS_COLORS = [
        'nuevo'               => 'bg-blue-100 text-blue-700',
        'evaluando'           => 'bg-indigo-100 text-indigo-700',
        'entrevista_agendada' => 'bg-yellow-100 text-yellow-700',
        'entrevistado'        => 'bg-orange-100 text-orange-700',
        'en_revision'         => 'bg-purple-100 text-purple-700',
        'aprobado'            => 'bg-teal-100 text-teal-700',
        'rechazado'           => 'bg-red-100 text-red-700',
        'contratado'          => 'bg-green-100 text-green-700',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(HrJobOpening::class, 'job_opening_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(HrProspectStatusLog::class, 'prospect_id')->latest();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(HrProspectEvaluation::class, 'prospect_id')->latest();
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(HrProspectInterview::class, 'prospect_id')->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(HrProspectNote::class, 'prospect_id')->latest();
    }

    public function references(): HasMany
    {
        return $this->hasMany(HrProspectReference::class, 'prospect_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    // ── Methods ────────────────────────────────────────────────────────────────

    /**
     * Change the prospect status and log it.
     */
    public function changeStatus(string $toStatus, ?string $reason = null, ?int $userId = null): void
    {
        $fromStatus = $this->status;

        if ($fromStatus === $toStatus) {
            return;
        }

        $this->update(['status' => $toStatus]);

        $this->statusLogs()->create([
            'user_id'     => $userId ?? auth()->id(),
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'reason'      => $reason,
        ]);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name} {$this->second_last_name}");
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->source
            ? self::SOURCES[$this->source] ?? $this->source
            : 'Sin fuente';
    }

    public function getInterviewTypeLabelAttribute(): ?string
    {
        return self::INTERVIEW_TYPES[$this->interview_type] ?? $this->interview_type;
    }
}
