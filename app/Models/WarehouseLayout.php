<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLayout extends Model
{
    protected $fillable = [
        'warehouse_id',
        'grid_cols',
        'grid_rows',
        'cell_size_cm',
        'polygon_points',
        'background_color',
        'wall_color',
        'floor_color',
    ];

    protected $casts = [
        'polygon_points'   => 'array',
        'grid_cols'        => 'integer',
        'grid_rows'        => 'integer',
        'cell_size_cm'     => 'integer',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function spots(): HasMany
    {
        return $this->hasMany(WarehouseSpot::class);
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    /** Ancho real del grid en cm */
    public function getRealWidthCmAttribute(): int
    {
        return $this->grid_cols * $this->cell_size_cm;
    }

    /** Alto real del grid en cm */
    public function getRealHeightCmAttribute(): int
    {
        return $this->grid_rows * $this->cell_size_cm;
    }

    /** Indica si el polígono tiene al menos 3 puntos (forma válida) */
    public function getHasPolygonAttribute(): bool
    {
        return count($this->polygon_points ?? []) >= 3;
    }

    /**
     * Construye el string de puntos SVG para un <polygon> o <polyline>.
     * Cada punto {col, row} se convierte a píxeles usando $pixelPerCell.
     */
    public function toSvgPoints(int $pixelPerCell = 32): string
    {
        return collect($this->polygon_points ?? [])
            ->map(fn($p) => ($p['col'] * $pixelPerCell) . ',' . ($p['row'] * $pixelPerCell))
            ->join(' ');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Genera automáticamente los niveles para todos los spots del layout
     * que aún no los tienen. Útil al importar o restaurar un layout.
     */
    public function syncSpotLevels(): void
    {
        $this->spots->each(fn(WarehouseSpot $spot) => $spot->syncLevels());
    }
}
