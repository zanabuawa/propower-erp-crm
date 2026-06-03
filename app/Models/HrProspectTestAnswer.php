<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrProspectTestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_prospect_test_id',
        'hr_test_attempt_id',
        'hr_test_question_id',
        'answer_text',
        'hr_test_option_id',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function prospectTest(): BelongsTo
    {
        return $this->belongsTo(HrProspectTest::class, 'hr_prospect_test_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(HrTestAttempt::class, 'hr_test_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(HrTestQuestion::class, 'hr_test_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(HrTestOption::class, 'hr_test_option_id');
    }
}
