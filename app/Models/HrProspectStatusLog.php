<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrProspectStatusLog extends Model
{
    protected $table = 'hr_prospect_status_logs';

    protected $fillable = [
        'prospect_id',
        'user_id',
        'from_status',
        'to_status',
        'reason',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(HrProspect::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFromStatusLabelAttribute(): ?string
    {
        return $this->from_status ? (HrProspect::STATUSES[$this->from_status] ?? $this->from_status) : null;
    }

    public function getToStatusLabelAttribute(): string
    {
        return HrProspect::STATUSES[$this->to_status] ?? $this->to_status;
    }
}
