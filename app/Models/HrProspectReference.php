<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrProspectReference extends Model
{
    protected $table = 'hr_prospect_references';

    protected $fillable = [
        'prospect_id', 'name', 'company', 'position', 'phone', 'relationship', 'notes', 'is_verified'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(HrProspect::class, 'prospect_id');
    }
}
