<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementItem extends Model
{
    protected $fillable = [
        'stock_movement_id', 'product_id', 'warehouse_id',
        'warehouse_destination_id', 'quantity', 'unit_price',
        'quantity_before', 'quantity_after',
        'received_quantity', 'received_at',
        'is_late_addition', 'added_at',
    ];

    protected $casts = [
        'quantity'          => 'decimal:4',
        'unit_price'        => 'decimal:2',
        'quantity_before'   => 'decimal:4',
        'quantity_after'    => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'received_at'       => 'datetime',
        'added_at'          => 'datetime',
        'is_late_addition'  => 'boolean',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseDestination(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_destination_id');
    }
}