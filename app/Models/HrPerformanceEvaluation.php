<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrPerformanceEvaluation extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_performance_evaluations';

    protected $fillable = [
        'company_id', 'employee_id', 'evaluator_id', 'period', 'evaluation_date',
        'categories', 'overall_score', 'strengths', 'areas_for_improvement',
        'goals_next_period', 'status', 'acknowledged_at',
    ];

    protected $casts = [
        'evaluation_date'  => 'date',
        'categories'       => 'array',
        'overall_score'    => 'decimal:2',
        'acknowledged_at'  => 'datetime',
    ];

    const CATEGORY_LABELS = [
        'attendance'    => 'Asistencia y puntualidad',
        'performance'   => 'Desempeño laboral',
        'teamwork'      => 'Trabajo en equipo',
        'initiative'    => 'Iniciativa y proactividad',
        'communication' => 'Comunicación',
        'quality'       => 'Calidad del trabajo',
        'leadership'    => 'Liderazgo',
    ];

    const STATUSES = [
        'draft'        => 'Borrador',
        'submitted'    => 'Enviada',
        'acknowledged' => 'Acusada',
    ];

    const STATUS_COLORS = [
        'draft'        => 'bg-gray-100 text-gray-600',
        'submitted'    => 'bg-blue-100 text-blue-700',
        'acknowledged' => 'bg-green-100 text-green-700',
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

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    public function getScoreLabelAttribute(): string
    {
        $s = (float) $this->overall_score;
        return match (true) {
            $s >= 90 => 'Excelente',
            $s >= 75 => 'Bueno',
            $s >= 60 => 'Regular',
            $s >= 40 => 'Deficiente',
            default  => 'Muy deficiente',
        };
    }

    public function getScoreColorAttribute(): string
    {
        $s = (float) $this->overall_score;
        return match (true) {
            $s >= 90 => 'text-green-600',
            $s >= 75 => 'text-blue-600',
            $s >= 60 => 'text-yellow-600',
            default  => 'text-red-600',
        };
    }

    /** Recalculate overall_score as average of categories */
    public function recalculateScore(): void
    {
        $categories = $this->categories ?? [];
        if (empty($categories)) return;
        $this->overall_score = round(array_sum($categories) / count($categories), 2);
        $this->save();
    }
}
