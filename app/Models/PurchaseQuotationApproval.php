<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseQuotationApproval extends Model
{
    protected $fillable = [
        'purchase_quotation_id', 'user_id', 'role', 'level',
        'status', 'comments', 'signature', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'level'      => 'integer',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(PurchaseQuotation::class, 'purchase_quotation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
