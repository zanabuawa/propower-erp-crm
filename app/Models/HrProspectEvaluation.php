<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrProspectEvaluation extends Model
{
    protected $table = 'hr_prospect_evaluations';

    protected $fillable = [
        'prospect_id',
        'evaluator_id',
        'score',
        'criteria_scores',
        'comments',
        'result',
    ];

    protected $casts = [
        'criteria_scores' => 'array',
        'score'           => 'decimal:2',
    ];

    const CRITERIA = [
        'knowledge'     => 'Conocimientos técnicos',
        'experience'    => 'Experiencia relevante',
        'communication' => 'Habilidades de comunicación',
        'attitude'      => 'Actitud y disposición',
        'fit'           => 'Ajuste cultural / equipo',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(HrProspect::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
