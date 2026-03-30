<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleQuotationItem extends Model
{
    protected $fillable = [
        'sale_quotation_id', 'product_id', 'description', 'quantity',
        'unit_price', 'discount_pct', 'discount_amount', 'tax_rate', 'subtotal', 'unit', 'notes',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'discount_pct'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function quotation(): BelongsTo { return $this->belongsTo(SaleQuotation::class, 'sale_quotation_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}