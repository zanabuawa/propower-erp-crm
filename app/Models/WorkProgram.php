<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkProgram extends Model
{
    const STATUSES = [
        'borrador' => 'Borrador',
        'vigente'  => 'Vigente',
        'historico' => 'Histórico',
    ];

    protected $fillable = [
        'project_id', 'tender_id', 'name', 'version',
        'status', 'created_by', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function project(): BelongsTo    { return $this->belongsTo(Project::class); }
    public function tender(): BelongsTo     { return $this->belongsTo(Tender::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function activities(): HasMany
    {
        return $this->hasMany(WorkProgramActivity::class, 'program_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    public function allActivities(): HasMany
    {
        return $this->hasMany(WorkProgramActivity::class, 'program_id')->orderBy('sort_order');
    }
}
