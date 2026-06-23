<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkProgramActivity extends Model
{
    protected $fillable = [
        'program_id', 'parent_id', 'name', 'unit', 'quantity',
        'start_date', 'end_date', 'actual_start_date', 'actual_end_date',
        'actual_notes', 'progress_pct', 'sort_order',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'actual_start_date' => 'date',
        'actual_end_date'   => 'date',
        'progress_pct'      => 'integer',
        'quantity'          => 'float',
    ];

    public function program(): BelongsTo    { return $this->belongsTo(WorkProgram::class, 'program_id'); }
    public function parent(): BelongsTo     { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany     { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }

    public function getDurationDaysAttribute(): ?int
    {
        if (! $this->start_date || ! $this->end_date) return null;
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getActualDurationDaysAttribute(): ?int
    {
        if (! $this->actual_start_date || ! $this->actual_end_date) return null;
        return $this->actual_start_date->diffInDays($this->actual_end_date) + 1;
    }
}
