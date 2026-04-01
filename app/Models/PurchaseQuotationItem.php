<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseQuotationItem extends Model
{
    protected $fillable = [
        'purchase_quotation_id', 'product_id', 'description',
        'quantity', 'unit', 'unit_price', 'tax_rate', 'subtotal',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(PurchaseQuotation::class, 'purchase_quotation_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
