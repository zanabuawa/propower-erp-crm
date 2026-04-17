<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrProspectInterview extends Model
{
    protected $table = 'hr_prospect_interviews';

    protected $fillable = [
        'prospect_id',
        'interviewer_id',
        'interview_date',
        'interview_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'interview_date' => 'datetime',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(HrProspect::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
