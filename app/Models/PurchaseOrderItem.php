<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'description',
        'quantity', 'quantity_received', 'unit_price', 'tax_rate', 'subtotal', 'unit',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'tax_rate'          => 'decimal:2',
        'subtotal'          => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPendingQuantityAttribute(): float
    {
        return $this->quantity - $this->quantity_received;
    }
}