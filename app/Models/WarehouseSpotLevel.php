<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseSpotLevel extends Model
{
    protected $fillable = [
        'warehouse_spot_id',
        'level_number',
        'label',
        'height_cm',
        'capacity_units',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'level_number'   => 'integer',
        'height_cm'      => 'integer',
        'capacity_units' => 'integer',
        'is_active'      => 'boolean',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function spot(): BelongsTo
    {
        return $this->belongsTo(WarehouseSpot::class, 'warehouse_spot_id');
    }

    public function levelProducts(): HasMany
    {
        return $this->hasMany(WarehouseSpotLevelProduct::class)->orderBy('sort_order');
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    /**
     * Devuelve los productos con su stock actual en el almacén de este nivel.
     * Enriquece cada producto con quantity del Stock correspondiente.
     */
    public function getProductsWithStockAttribute()
    {
        $warehouseId = $this->spot->warehouse_id;

        return $this->levelProducts()
            ->with(['product' => fn($q) => $q->with([
                'stocks' => fn($s) => $s->where('warehouse_id', $warehouseId),
            ])])
            ->get()
            ->map(function (WarehouseSpotLevelProduct $lp) {
                $stock = $lp->product->stocks->first();
                $lp->product->current_stock  = $stock?->quantity ?? 0;
                $lp->product->available_stock = $stock?->available_quantity ?? 0;
                return $lp;
            });
    }

    /** Cantidad de productos distintos asignados a este nivel */
    public function getProductCountAttribute(): int
    {
        return $this->levelProducts()->count();
    }

    /** Etiqueta de visualización: custom o "Nivel N" */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: "Nivel {$this->level_number}";
    }
}
