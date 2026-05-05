<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WarehouseSpot extends Model
{
    protected $fillable = [
        'warehouse_id',
        'warehouse_layout_id',
        'type',
        'label',
        'code',
        'col',
        'row',
        'width_cells',
        'depth_cells',
        'rotation',
        'levels_count',
        'sections_count',
        'level_height_cm',
        'total_height_cm',
        'color',
        'is_locked',
        'notes',
    ];

    protected $casts = [
        'col'             => 'integer',
        'row'             => 'integer',
        'width_cells'     => 'integer',
        'depth_cells'     => 'integer',
        'rotation'        => 'integer',
        'levels_count'    => 'integer',
        'sections_count'  => 'integer',
        'level_height_cm' => 'integer',
        'total_height_cm' => 'integer',
        'is_locked'       => 'boolean',
    ];

    public const TYPES = [
        'estanteria' => 'Estantería',
        'rack'       => 'Rack',
        'armario'    => 'Armario',
        'mesa'       => 'Mesa de trabajo',
        'area'       => 'Área',
        'otro'       => 'Otro',
    ];

    public const TYPE_COLORS = [
        'estanteria' => '#6366F1',
        'rack'       => '#F59E0B',
        'armario'    => '#10B981',
        'mesa'       => '#3B82F6',
        'area'       => '#94A3B8',
        'otro'       => '#8B5CF6',
    ];

    public const TYPE_ICONS = [
        'estanteria' => 'M4 7h16M4 12h16M4 17h16',
        'rack'       => 'M3 4h18v4H3zM3 10h18v4H3zM3 16h18v4H3z',
        'armario'    => 'M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z M12 3v18',
        'mesa'       => 'M3 10h18M5 10v7m14-7v7M3 10l2-4h14l2 4',
        'area'       => 'M3 3h18v18H3z',
        'otro'       => 'M12 4v16M4 12h16',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(WarehouseLayout::class, 'warehouse_layout_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(WarehouseSpotLevel::class)->orderBy('level_number');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            WarehouseSpotLevelProduct::class,
            WarehouseSpotLevel::class,
            'warehouse_spot_id',      // FK en warehouse_spot_levels
            'warehouse_spot_level_id' // FK en warehouse_spot_level_products
        );
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? '#6366F1';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Otro';
    }

    /** Alto total calculado: niveles × alto por nivel */
    public function getCalculatedHeightCmAttribute(): int
    {
        return $this->levels_count * $this->level_height_cm;
    }

    /**
     * Posición SVG en píxeles (esquina superior-izquierda).
     * $ppc = pixels per cell
     */
    public function getSvgX(int $ppc = 32): int
    {
        return $this->col * $ppc;
    }

    public function getSvgY(int $ppc = 32): int
    {
        return $this->row * $ppc;
    }

    public function getSvgWidth(int $ppc = 32): int
    {
        return $this->width_cells * $ppc;
    }

    public function getSvgHeight(int $ppc = 32): int
    {
        return $this->depth_cells * $ppc;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Sincroniza los registros de WarehouseSpotLevel con levels_count.
     * Crea los que faltan, elimina los sobrantes.
     */
    public function syncLevels(): void
    {
        $existing = $this->levels()->pluck('level_number')->toArray();
        $desired  = range(1, $this->levels_count);

        // Crear niveles faltantes
        foreach (array_diff($desired, $existing) as $n) {
            $this->levels()->create([
                'level_number' => $n,
                'label'        => "Nivel {$n}",
                'height_cm'    => $this->level_height_cm,
            ]);
        }

        // Eliminar niveles que ya no existen (si se redujo levels_count)
        if ($toDelete = array_diff($existing, $desired)) {
            $this->levels()->whereIn('level_number', $toDelete)->delete();
        }
    }

    protected static function booted(): void
    {
        // Al crear un spot, generar sus niveles automáticamente
        static::created(function (WarehouseSpot $spot) {
            $spot->syncLevels();
        });

        // Al actualizar levels_count o level_height_cm, re-sincronizar
        static::updated(function (WarehouseSpot $spot) {
            if ($spot->wasChanged(['levels_count', 'level_height_cm'])) {
                $spot->syncLevels();
                if ($spot->wasChanged('level_height_cm')) {
                    $spot->levels()
                        ->whereNull('height_cm')
                        ->orWhere('height_cm', $spot->getOriginal('level_height_cm'))
                        ->update(['height_cm' => $spot->level_height_cm]);
                }
            }
        });
    }
}
