<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrEvaluationProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_prospect_id',
        'hr_employee_id',
        'current_stage_index',
        'total_stages',
        'status',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(HrProspect::class, 'hr_prospect_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'hr_employee_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(HrEvaluationStage::class)->orderBy('order');
    }
}
