<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrProspectTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_evaluation_stage_id',
        'hr_test_template_id',
        'score',
        'status',
        'graded_by_id',
        'max_attempts',
        'attempts_count',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(HrEvaluationStage::class, 'hr_evaluation_stage_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HrTestTemplate::class, 'hr_test_template_id');
    }

    public function testTemplate(): BelongsTo
    {
        return $this->template();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(HrProspectTestAnswer::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(HrTestAttempt::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_id');
    }
}
