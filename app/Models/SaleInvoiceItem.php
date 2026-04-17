<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleInvoiceItem extends Model
{
    protected $fillable = [
        'sale_invoice_id', 'product_id', 'description', 'quantity',
        'unit_price', 'discount_pct', 'discount_amount', 'ieps_rate', 'ieps_amount',
        'tax_rate', 'subtotal', 'unit',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'discount_pct'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'ieps_rate'       => 'decimal:4',
        'ieps_amount'     => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(SaleInvoice::class, 'sale_invoice_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}