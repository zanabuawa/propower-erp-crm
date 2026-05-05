<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseSpotLevelProduct extends Model
{
    protected $table = 'warehouse_spot_level_products';

    protected $fillable = [
        'warehouse_spot_level_id',
        'product_id',
        'sort_order',
        'assigned_qty',
        'section',
        'notes',
    ];

    protected $casts = [
        'sort_order'   => 'integer',
        'assigned_qty' => 'integer',
        'section'      => 'integer',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function level(): BelongsTo
    {
        return $this->belongsTo(WarehouseSpotLevel::class, 'warehouse_spot_level_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Retorna el stock actual del producto en el almacén del nivel.
     * Shortcut para evitar múltiples consultas en el blade.
     */
    public function getStockInWarehouseAttribute(): float
    {
        $warehouseId = $this->level->spot->warehouse_id;

        return (float) Stock::where('product_id', $this->product_id)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0.0;
    }

    public function getAvailableInWarehouseAttribute(): float
    {
        $warehouseId = $this->level->spot->warehouse_id;

        $stock = Stock::where('product_id', $this->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $stock?->available_quantity ?? 0.0;
    }
}
