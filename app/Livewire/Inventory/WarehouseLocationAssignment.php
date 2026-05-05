<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseSpot;
use App\Models\WarehouseSpotLevel;
use App\Models\WarehouseSpotLevelProduct;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WarehouseLocationAssignment extends Component
{
    public Warehouse $warehouse;
    public ?int    $selectedSpotId  = null;
    public ?int    $selectedLevelId = null;
    public int     $selectedSection = 1;
    public bool    $showInventoryModal = false;
    public string  $productSearch   = '';
    public string  $categoryFilter  = '';

    public function mount(Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function selectSpot(int $spotId): void
    {
        $this->selectedSpotId  = $spotId;
        $this->selectedLevelId = null;
        $this->selectedSection = 1;
    }

    public function selectLevelSection(int $levelId, int $section): void
    {
        $this->selectedLevelId = $levelId;
        $this->selectedSection = $section;
    }

    public function assignProduct(int $productId, ?int $assignedQty = null): void
    {
        if (!$this->selectedLevelId) return;

        $exists = WarehouseSpotLevelProduct::where('warehouse_spot_level_id', $this->selectedLevelId)
            ->where('product_id', $productId)
            ->where('section', $this->selectedSection)
            ->exists();
        if ($exists) return;

        $maxSort = WarehouseSpotLevelProduct::where('warehouse_spot_level_id', $this->selectedLevelId)
            ->where('section', $this->selectedSection)
            ->max('sort_order') ?? 0;

        WarehouseSpotLevelProduct::create([
            'warehouse_spot_level_id' => $this->selectedLevelId,
            'product_id'              => $productId,
            'sort_order'              => $maxSort + 1,
            'assigned_qty'            => $assignedQty > 0 ? $assignedQty : null,
            'section'                 => $this->selectedSection,
        ]);

        $this->dispatchSpotUpdate();
        session()->flash('success', 'Producto asignado.');
    }

    public function updateProductQty(int $levelProductId, int $qty): void
    {
        WarehouseSpotLevelProduct::where('id', $levelProductId)
            ->whereHas('level.spot', fn($q) => $q->where('warehouse_id', $this->warehouse->id))
            ->update(['assigned_qty' => $qty > 0 ? $qty : null]);

        session()->flash('success', 'Cantidad actualizada.');
    }

    public function removeProduct(int $levelProductId): void
    {
        WarehouseSpotLevelProduct::where('id', $levelProductId)
            ->whereHas('level.spot', fn($q) => $q->where('warehouse_id', $this->warehouse->id))
            ->delete();

        $this->dispatchSpotUpdate();
        session()->flash('success', 'Producto removido de la ubicación.');
    }

    public function saveAll(): void
    {
        session()->flash('success', 'Asignaciones guardadas correctamente.');
    }

    private function dispatchSpotUpdate(): void
    {
        if (!$this->selectedSpotId) return;
        $count = WarehouseSpot::withCount('products')->find($this->selectedSpotId)?->products_count ?? 0;
        $this->dispatch('spot-assignment-updated', spotId: $this->selectedSpotId, hasProducts: $count > 0);
    }

    public function render()
    {
        $layout     = $this->warehouse->layout;
        $spots      = collect();
        $polygons   = [];
        $gridCols   = 24; $gridRows = 20;
        $wallColor  = '#1E293B'; $floorColor = '#FFFFFF'; $bgColor = '#FAFBFC';

        if ($layout) {
            $gridCols   = $layout->grid_cols;
            $gridRows   = $layout->grid_rows;
            $wallColor  = $layout->wall_color;
            $floorColor = $layout->floor_color;
            $bgColor    = $layout->background_color ?? '#FAFBFC';
            $raw        = $layout->polygon_points ?? [];

            // Normalize old single-array format
            if (!empty($raw) && isset($raw[0]['col'])) {
                $raw = [['id' => 1, 'label' => 'Zona 1', 'points' => $raw, 'floorColor' => $floorColor, 'wallColor' => $wallColor]];
            }
            $polygons = $raw;

            $spots = WarehouseSpot::where('warehouse_layout_id', $layout->id)
                ->withCount('products')
                ->get()
                ->map(fn($s) => [
                    'id'           => $s->id,
                    'type'         => $s->type,
                    'label'        => $s->label,
                    'code'         => $s->code,
                    'col'          => $s->col,
                    'row'          => $s->row,
                    'width_cells'  => $s->width_cells,
                    'depth_cells'  => $s->depth_cells,
                    'levels_count'  => $s->levels_count,
                    'sections_count' => $s->sections_count ?? 1,
                    'color'         => $s->color,
                    'has_products'  => $s->products_count > 0,
                ]);
        }

        $selectedSpot = null;
        $spotLevels   = collect();
        if ($this->selectedSpotId) {
            $selectedSpot = WarehouseSpot::find($this->selectedSpotId);
            $spotLevels   = WarehouseSpotLevel::with(['levelProducts.product.unitOfMeasure', 'levelProducts.product.primaryImage'])
                ->where('warehouse_spot_id', $this->selectedSpotId)
                ->orderBy('level_number')
                ->get();
        }

        // Product IDs placed anywhere in this warehouse
        $placedProductIds = WarehouseSpotLevelProduct::whereHas(
                'level.spot', fn($q) => $q->where('warehouse_id', $this->warehouse->id)
            )->pluck('product_id')->unique()->values()->toArray();

        // Total quantity assigned to locations per product in this warehouse
        $locatedQtys = WarehouseSpotLevelProduct::whereHas(
                'level.spot', fn($q) => $q->where('warehouse_id', $this->warehouse->id)
            )->whereNotNull('assigned_qty')
             ->groupBy('product_id')
             ->selectRaw('product_id, SUM(assigned_qty) as total')
             ->pluck('total', 'product_id')
             ->map(fn($v) => (int) $v)
             ->toArray();

        // Catalog: products with stock in this warehouse
        $catalogQuery = Product::where('company_id', $this->warehouse->company_id)
            ->where('is_active', true)
            ->with([
                'stocks'       => fn($q) => $q->where('warehouse_id', $this->warehouse->id),
                'primaryImage',
                'category',
                'unitOfMeasure',
            ])
            ->whereHas('stocks', fn($q) => $q->where('warehouse_id', $this->warehouse->id)->where('quantity', '>', 0));

        if ($this->productSearch) {
            $s = $this->productSearch;
            $catalogQuery->where(fn($q) => $q
                ->where('name', 'like', "%$s%")
                ->orWhere('sku', 'like', "%$s%")
            );
        }
        if ($this->categoryFilter) {
            $catalogQuery->where('category_id', $this->categoryFilter);
        }

        $availableProducts = $catalogQuery->orderBy('name')->get()->values();

        $categories = Category::where('company_id', $this->warehouse->company_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.warehouse-location-assignment', compact(
            'layout', 'spots', 'polygons',
            'gridCols', 'gridRows', 'wallColor', 'floorColor', 'bgColor',
            'selectedSpot', 'spotLevels',
            'placedProductIds', 'locatedQtys',
            'availableProducts',
            'categories',
        ) + ['selectedSection' => $this->selectedSection]);
    }
}
