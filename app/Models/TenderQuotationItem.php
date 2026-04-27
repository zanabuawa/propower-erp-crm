<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderQuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'tender_item_id', 'description',
        'unit', 'quantity', 'unit_price', 'total', 'sort_order',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'total'      => 'float',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(TenderQuotation::class, 'quotation_id');
    }

    public function tenderItem(): BelongsTo
    {
        return $this->belongsTo(TenderItem::class, 'tender_item_id');
    }
}
