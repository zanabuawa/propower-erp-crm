<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrEvaluationStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_evaluation_process_id',
        'name',
        'order',
        'scheduled_at',
        'guide_path',
        'guide_paths',
        'video_links',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'guide_paths' => 'array',
        'video_links' => 'array',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(HrEvaluationProcess::class, 'hr_evaluation_process_id');
    }

    public function evaluationProcess(): BelongsTo
    {
        return $this->process();
    }

    public function prospectTests(): HasMany
    {
        return $this->hasMany(HrProspectTest::class);
    }

    public function isAvailable(): bool
    {
        return $this->scheduled_at !== null && $this->scheduled_at->lte(now());
    }

    public function getAvailabilityLabelAttribute(): string
    {
        if (! $this->scheduled_at) {
            return 'Pendiente de programacion';
        }

        if ($this->scheduled_at->isFuture()) {
            return 'Disponible ' . $this->scheduled_at->format('d/m/Y H:i');
        }

        return 'Disponible';
    }
}
