<?php

namespace App\Livewire\Inventory;

use App\Models\Warehouse;
use App\Models\WarehouseLayout;
use App\Models\WarehouseSpot;
use App\Models\WarehouseSpotLevel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WarehouseLayoutEditor extends Component
{
    public Warehouse $warehouse;
    public ?WarehouseLayout $layout = null;

    // Grid config (editable)
    public int    $gridCols   = 24;
    public int    $gridRows   = 20;
    public int    $cellSizeCm = 50;
    public string $wallColor  = '#1E293B';
    public string $floorColor = '#FFFFFF';
    public string $bgColor    = '#F1F5F9';

    // Spot config panel (right panel)
    public bool   $showSpotPanel    = false;
    public ?int   $selectedSpotId   = null;
    public string $spotLabel        = '';
    public string $spotCode         = '';
    public string $spotType         = 'estanteria';
    public string $spotColor        = '#6366F1';
    public int    $spotLevels       = 3;
    public int    $spotSections     = 1;
    public int    $spotLevelHeight  = 40;
    public int    $spotWidth        = 3;
    public int    $spotDepth        = 1;
    public string $spotNotes        = '';

    public bool $showGridPanel = false;

    // Plan data
    public array $polygons = [];   // array of { id, label, points[], floorColor, wallColor }
    public array $spots    = [];

    public function mount(Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
        $layout = $warehouse->layout;

        if ($layout) {
            $this->layout     = $layout;
            $this->gridCols   = $layout->grid_cols;
            $this->gridRows   = $layout->grid_rows;
            $this->cellSizeCm = $layout->cell_size_cm;
            $this->wallColor  = $layout->wall_color;
            $this->floorColor = $layout->floor_color;
            $this->bgColor    = $layout->background_color;
            $this->polygons   = $this->normalizePolygons($layout->polygon_points ?? []);
        }

        $this->loadSpots();
    }

    // Backward-compat: old format was [{col,row}], new format is [{id,label,points[]}]
    private function normalizePolygons(array $raw): array
    {
        if (empty($raw)) return [];
        // Detect old format: first element has 'col' key instead of 'points'
        if (isset($raw[0]['col'])) {
            return [[
                'id'         => 1,
                'label'      => 'Zona 1',
                'points'     => $raw,
                'floorColor' => $this->floorColor,
                'wallColor'  => $this->wallColor,
            ]];
        }
        return $raw;
    }

    public function loadSpots(): void
    {
        $this->spots = $this->layout
            ? WarehouseSpot::where('warehouse_layout_id', $this->layout->id)
                ->get()
                ->map(fn($s) => $this->spotToArray($s))
                ->values()
                ->toArray()
            : [];
    }

    // ── Layout / Polygon ─────────────────────────────────────────────────────

    public function savePolygons(array $polygons): void
    {
        $this->polygons = $polygons;
        $this->layout = WarehouseLayout::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id],
            [
                'grid_cols'        => $this->gridCols,
                'grid_rows'        => $this->gridRows,
                'cell_size_cm'     => $this->cellSizeCm,
                'polygon_points'   => $polygons,
                'wall_color'       => $this->wallColor,
                'floor_color'      => $this->floorColor,
                'background_color' => $this->bgColor,
            ]
        );
        session()->flash('success', 'Zonas guardadas.');
    }

    public function saveLayout(array $polygons, array $spots): void
    {
        $this->polygons = $polygons;
        $this->layout = WarehouseLayout::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id],
            [
                'grid_cols'        => $this->gridCols,
                'grid_rows'        => $this->gridRows,
                'cell_size_cm'     => $this->cellSizeCm,
                'polygon_points'   => $this->polygons,
                'wall_color'       => $this->wallColor,
                'floor_color'      => $this->floorColor,
                'background_color' => $this->bgColor,
            ]
        );

        foreach ($spots as $sData) {
            if (!isset($sData['id'])) continue;
            WarehouseSpot::where('id', $sData['id'])
                ->where('warehouse_id', $this->warehouse->id)
                ->update([
                    'col'         => $sData['col'],
                    'row'         => $sData['row'],
                    'width_cells' => $sData['width_cells'],
                    'depth_cells' => $sData['depth_cells'],
                    'rotation'    => $sData['rotation'] ?? 0,
                ]);
        }

        $this->loadSpots();
        session()->flash('success', 'Plano guardado correctamente.');
    }

    public function saveGridConfig(): void
    {
        $this->validate([
            'gridCols'   => 'required|integer|min:5|max:60',
            'gridRows'   => 'required|integer|min:5|max:50',
            'cellSizeCm' => 'required|integer|min:25|max:200',
        ]);

        if ($this->layout) {
            $this->layout->update([
                'grid_cols'        => $this->gridCols,
                'grid_rows'        => $this->gridRows,
                'cell_size_cm'     => $this->cellSizeCm,
                'wall_color'       => $this->wallColor,
                'floor_color'      => $this->floorColor,
                'background_color' => $this->bgColor,
            ]);
        }

        $this->showGridPanel = false;

        // Notify Alpine to update SVG dimensions (SVG has wire:ignore so Livewire won't morph it)
        $this->dispatch('grid-config-updated',
            cols:       $this->gridCols,
            rows:       $this->gridRows,
            wallColor:  $this->wallColor,
            floorColor: $this->floorColor,
            bgColor:    $this->bgColor,
        );

        session()->flash('success', 'Configuración de cuadrícula guardada.');
    }

    // ── Spots ────────────────────────────────────────────────────────────────

    public function addSpot(string $type, int $col, int $row): void
    {
        // Si no existe el layout (por ejemplo, acabas de dibujar el polígono), lo creamos al vuelo
        if (!$this->layout) {
            $this->saveLayout($this->polygons, []);
            $this->layout = $this->warehouse->fresh()->layout;
        }

        if (!$this->layout) return;

        $count = WarehouseSpot::where('warehouse_layout_id', $this->layout->id)
            ->where('type', $type)->count() + 1;

        $typeLabel = WarehouseSpot::TYPES[$type] ?? 'Elemento';
        $prefix    = match($type) {
            'estanteria' => 'EST',
            'rack'       => 'RCK',
            'armario'    => 'ARM',
            'mesa'       => 'MSA',
            'area'       => 'ARE',
            default      => 'OTR',
        };

        $spot = WarehouseSpot::create([
            'warehouse_id'        => $this->warehouse->id,
            'warehouse_layout_id' => $this->layout->id,
            'type'                => $type,
            'label'               => "{$typeLabel} {$count}",
            'code'                => "{$prefix}-" . str_pad($count, 2, '0', STR_PAD_LEFT),
            'col'                 => $col,
            'row'                 => $row,
            'width_cells'         => $type === 'area' ? 4 : 3,
            'depth_cells'         => $type === 'area' ? 3 : 1,
            'rotation'            => 0,
            'levels_count'        => $type === 'mesa' ? 1 : 3,
            'level_height_cm'     => 40,
            'color'               => WarehouseSpot::TYPE_COLORS[$type] ?? '#6366F1',
        ]);

        $this->loadSpots();
        $this->dispatch('spot-created', spot: $this->spotToArray($spot));
    }

    public function moveSpot(int $spotId, int $col, int $row): void
    {
        WarehouseSpot::where('id', $spotId)
            ->where('warehouse_id', $this->warehouse->id)
            ->update(['col' => $col, 'row' => $row]);
    }

    public function resizeSpot(int $spotId, int $widthCells, int $depthCells): void
    {
        $spot = WarehouseSpot::where('id', $spotId)
            ->where('warehouse_id', $this->warehouse->id)
            ->firstOrFail();

        $spot->update([
            'width_cells' => max(1, $widthCells),
            'depth_cells' => max(1, $depthCells),
        ]);

        // Sync panel fields if this is the currently selected spot
        if ($this->selectedSpotId === $spotId) {
            $this->spotWidth = max(1, $widthCells);
            $this->spotDepth = max(1, $depthCells);
        }

        $this->dispatch('spot-updated', spot: $this->spotToArray($spot->fresh()));
    }

    public function rotateSpot(int $spotId): void
    {
        $spot = WarehouseSpot::where('id', $spotId)
            ->where('warehouse_id', $this->warehouse->id)
            ->firstOrFail();

        $spot->update(['rotation' => ($spot->rotation + 90) % 360]);
        $this->dispatch('spot-updated', spot: $this->spotToArray($spot->fresh()));
    }

    public function toggleLockSpot(int $spotId): void
    {
        $spot = WarehouseSpot::where('id', $spotId)
            ->where('warehouse_id', $this->warehouse->id)
            ->firstOrFail();

        $spot->update(['is_locked' => !$spot->is_locked]);
        $this->dispatch('spot-updated', spot: $this->spotToArray($spot->fresh()));
    }

    public function selectSpot(int $spotId): void
    {
        $spot = WarehouseSpot::where('warehouse_id', $this->warehouse->id)
            ->findOrFail($spotId);

        $this->selectedSpotId  = $spotId;
        $this->spotLabel       = $spot->label;
        $this->spotCode        = $spot->code ?? '';
        $this->spotType        = $spot->type;
        $this->spotColor       = $spot->color;
        $this->spotLevels      = $spot->levels_count;
        $this->spotSections    = $spot->sections_count ?? 1;
        $this->spotLevelHeight = $spot->level_height_cm;
        $this->spotWidth       = $spot->width_cells;
        $this->spotDepth       = $spot->depth_cells;
        $this->spotNotes       = $spot->notes ?? '';
        $this->showSpotPanel   = true;
    }

    public function updateSpot(): void
    {
        $this->validate([
            'spotLabel'       => 'required|string|max:100',
            'spotCode'        => 'nullable|string|max:20',
            'spotLevels'      => 'required|integer|min:1|max:20',
            'spotSections'    => 'required|integer|min:1|max:20',
            'spotLevelHeight' => 'required|integer|min:10|max:500',
            'spotColor'       => 'required|string|max:7',
            'spotWidth'       => 'required|integer|min:1|max:30',
            'spotDepth'       => 'required|integer|min:1|max:30',
        ], [
            'spotLabel.required' => 'El nombre es obligatorio.',
            'spotLevels.min'     => 'Mínimo 1 nivel.',
            'spotLevels.max'     => 'Máximo 20 niveles.',
            'spotWidth.min'      => 'Mínimo 1 celda de ancho.',
            'spotDepth.min'      => 'Mínimo 1 celda de fondo.',
        ]);

        $spot = WarehouseSpot::where('id', $this->selectedSpotId)
            ->where('warehouse_id', $this->warehouse->id)
            ->firstOrFail();

        $spot->update([
            'label'           => $this->spotLabel,
            'code'            => $this->spotCode ?: null,
            'type'            => $this->spotType,
            'color'           => $this->spotColor,
            'levels_count'    => $this->spotLevels,
            'sections_count'  => $this->spotSections,
            'level_height_cm' => $this->spotLevelHeight,
            'width_cells'     => max(1, $this->spotWidth),
            'depth_cells'     => max(1, $this->spotDepth),
            'notes'           => $this->spotNotes ?: null,
        ]);
        // boot hook calls syncLevels() if levels_count changed

        $this->dispatch('spot-updated', spot: $this->spotToArray($spot->fresh()));
        session()->flash('success', "'{$this->spotLabel}' actualizado.");
    }

    public function deleteSpot(int $spotId): void
    {
        WarehouseSpot::where('id', $spotId)
            ->where('warehouse_id', $this->warehouse->id)
            ->delete();

        $this->showSpotPanel  = false;
        $this->selectedSpotId = null;
        $this->dispatch('spot-deleted', spotId: $spotId);
    }

    public function closeSpotPanel(): void
    {
        $this->showSpotPanel  = false;
        $this->selectedSpotId = null;
        $this->dispatch('spot-deselected');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function spotToArray(WarehouseSpot $spot): array
    {
        return [
            'id'              => $spot->id,
            'type'            => $spot->type,
            'label'           => $spot->label,
            'code'            => $spot->code,
            'col'             => $spot->col,
            'row'             => $spot->row,
            'width_cells'     => $spot->width_cells,
            'depth_cells'     => $spot->depth_cells,
            'rotation'        => $spot->rotation,
            'levels_count'    => $spot->levels_count,
            'sections_count'  => $spot->sections_count ?? 1,
            'level_height_cm' => $spot->level_height_cm,
            'color'           => $spot->color,
            'is_locked'       => $spot->is_locked,
        ];
    }

    public function render()
    {
        $selectedSpotLevels = null;
        if ($this->selectedSpotId && $this->showSpotPanel) {
            $warehouseId = $this->warehouse->id;
            $selectedSpotLevels = WarehouseSpotLevel::with([
                    'levelProducts.product' => fn($q) => $q->with([
                        'stocks' => fn($s) => $s->where('warehouse_id', $warehouseId),
                    ]),
                ])
                ->where('warehouse_spot_id', $this->selectedSpotId)
                ->orderBy('level_number')
                ->get();
        }

        return view('livewire.inventory.warehouse-layout-editor',
            compact('selectedSpotLevels')
        );
    }
}
