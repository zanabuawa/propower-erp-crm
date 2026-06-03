<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrJobOpening extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_job_openings';

    protected $fillable = [
        'company_id', 'position_id', 'branch_id', 'project_id', 'title', 'description',
        'requirements', 'benefits', 'quantity', 'salary_range',
        'type', 'status', 'published_at', 'closing_date', 'created_by'
    ];

    protected $casts = [
        'published_at' => 'date',
        'closing_date' => 'date',
        'quantity'     => 'integer',
    ];

    const TYPES = [
        'internal' => 'Interna',
        'external' => 'Externa',
        'mixed'    => 'Mixta',
    ];

    const STATUSES = [
        'open'      => 'Abierta',
        'closed'    => 'Cerrada',
        'paused'    => 'Pausada',
        'cancelled' => 'Cancelada',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(HrProspect::class, 'job_opening_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
