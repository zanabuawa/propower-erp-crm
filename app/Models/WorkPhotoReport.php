<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WorkPhotoReport extends Model
{
    protected $fillable = [
        'project_id', 'tender_id', 'report_date', 'week_start', 'week_end', 'title',
        'description', 'custom_body', 'photos', 'photo_layout', 'location', 'created_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'week_start' => 'date',
        'week_end' => 'date',
        'photos'      => 'array',
        'photo_layout' => 'array',
    ];

    public function project(): BelongsTo    { return $this->belongsTo(Project::class); }
    public function tender(): BelongsTo     { return $this->belongsTo(Tender::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }

    public function getPhotoUrlsAttribute(): array
    {
        return collect($this->photos ?? [])->map(fn($p) => Storage::url($p))->toArray();
    }
}
