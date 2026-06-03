<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrTestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_prospect_test_id',
        'attempt_number',
        'score',
        'status',
        'started_at',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function prospectTest(): BelongsTo
    {
        return $this->belongsTo(HrProspectTest::class, 'hr_prospect_test_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(HrProspectTestAnswer::class, 'hr_test_attempt_id');
    }
}
