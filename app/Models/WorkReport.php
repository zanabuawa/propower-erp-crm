<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkReport extends Model
{
    protected $fillable = [
        'project_id', 'tender_id', 'week_start', 'week_end',
        'progress_pct', 'activities', 'issues', 'next_week_plan',
        'weather_conditions', 'workers_count', 'created_by',
    ];

    protected $casts = [
        'week_start'   => 'date',
        'week_end'     => 'date',
        'progress_pct' => 'integer',
        'workers_count' => 'integer',
    ];

    public function project(): BelongsTo    { return $this->belongsTo(Project::class); }
    public function tender(): BelongsTo     { return $this->belongsTo(Tender::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
}
