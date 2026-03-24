<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id', 'product_id', 'warehouse_id',
        'description', 'quantity', 'unit_price', 'notes',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}