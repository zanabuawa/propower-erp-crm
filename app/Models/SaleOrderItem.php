<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleOrderItem extends Model
{
    protected $fillable = [
        'sale_order_id', 'product_id', 'description', 'quantity', 'quantity_delivered',
        'unit_price', 'discount_pct', 'discount_amount', 'tax_rate', 'subtotal', 'unit',
    ];

    protected $casts = [
        'quantity'           => 'decimal:2',
        'quantity_delivered' => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'discount_pct'       => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'tax_rate'           => 'decimal:2',
        'subtotal'           => 'decimal:2',
    ];

    public function order(): BelongsTo { return $this->belongsTo(SaleOrder::class, 'sale_order_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function getPendingQuantityAttribute(): float
    {
        return $this->quantity - $this->quantity_delivered;
    }
}