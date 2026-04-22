<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'parent_task_id', 'title', 'description',
        'status', 'priority', 'assigned_to', 'start_date', 'due_date',
        'completed_at', 'progress', 'estimated_hours', 'actual_hours', 'sort_order',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'due_date'        => 'date',
        'completed_at'    => 'date',
        'progress'        => 'integer',
        'estimated_hours' => 'decimal:2',
        'actual_hours'    => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id')->orderBy('sort_order');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class, 'task_id');
    }
}
