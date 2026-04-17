<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrProspectNote extends Model
{
    protected $table = 'hr_prospect_notes';

    protected $fillable = [
        'prospect_id',
        'user_id',
        'content',
    ];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(HrProspect::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
