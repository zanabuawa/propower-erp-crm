<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrTestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_test_template_id',
        'question_text',
        'type',
        'points',
        'order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(HrTestTemplate::class, 'hr_test_template_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(HrTestOption::class);
    }
}
