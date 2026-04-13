<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    protected $fillable = [
        'project_id', 'name', 'description', 'due_date',
        'completed_at', 'status', 'payment_amount', 'sort_order',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'completed_at'   => 'date',
        'payment_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pendiente' && $this->due_date->isPast();
    }
}
