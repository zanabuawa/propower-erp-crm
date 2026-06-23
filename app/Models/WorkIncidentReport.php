<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkIncidentReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'tender_id',
        'incident_date',
        'title',
        'location',
        'description',
        'actions_taken',
        'responsible_name',
        'status',
        'created_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
