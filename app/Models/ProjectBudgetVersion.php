<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBudgetVersion extends Model
{
    protected $fillable = [
        'project_id', 'version', 'name', 'description',
        'status', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'version'     => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProjectBudgetLine::class, 'version_id')->orderBy('category')->orderBy('sort_order');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->lines->sum('budgeted_amount');
    }
}
