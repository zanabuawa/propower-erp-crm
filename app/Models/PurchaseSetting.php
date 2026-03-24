<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseSetting extends Model
{
    protected $fillable = [
        'company_id', 'currency', 'level1_amount', 'level2_amount',
    ];

    protected $casts = [
        'level1_amount' => 'decimal:2',
        'level2_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getApprovalLevel(float $amount): int
    {
        if ($amount < $this->level1_amount) return 1;
        if ($amount < $this->level2_amount) return 2;
        return 3;
    }
}