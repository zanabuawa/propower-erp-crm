<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDeliveryItem extends Model
{
    protected $fillable = [
        'sale_delivery_id', 'sale_order_item_id', 'product_id', 'warehouse_id', 'quantity',
    ];

    protected $casts = ['quantity' => 'decimal:2'];

    public function delivery(): BelongsTo { return $this->belongsTo(SaleDelivery::class, 'sale_delivery_id'); }
    public function orderItem(): BelongsTo { return $this->belongsTo(SaleOrderItem::class, 'sale_order_item_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}