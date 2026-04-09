<?php

namespace App\Livewire\Inventory;

use App\Models\PepsKardex;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class PepsKardexView extends Component
{
    use WithPagination;

    // ── Filtros ──────────────────────────────────────────────────────────────

    #[Url]
    public ?int $productId = null;

    #[Url]
    public ?int $warehouseId = null;

    #[Url]
    public string $filterType = '';

    #[Url]
    public string $filterDirection = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public string $productSearch = '';
    public array  $productResults = [];

    // ── Producto seleccionado (cargado para mostrar su nombre) ────────────────
    public ?string $selectedProductName = null;

    public function mount(): void
    {
        if ($this->productId) {
            $this->selectedProductName = Product::find($this->productId)?->name;
        }
    }

    public function updatedProductId(): void   { $this->resetPage(); }
    public function updatedWarehouseId(): void  { $this->resetPage(); }
    public function updatedFilterType(): void   { $this->resetPage(); }
    public function updatedFilterDirection(): void { $this->resetPage(); }
    public function updatedDateFrom(): void     { $this->resetPage(); }
    public function updatedDateTo(): void       { $this->resetPage(); }

    public function updatedProductSearch(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->productResults = [];
            return;
        }

        $this->productResults = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(fn($q) => $q
                ->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->limit(8)
            ->get(['id', 'name', 'sku'])
            ->toArray();
    }

    public function selectProduct(int $id, string $name): void
    {
        $this->productId           = $id;
        $this->selectedProductName = $name;
        $this->productSearch       = '';
        $this->productResults      = [];
        $this->resetPage();
    }

    public function clearProduct(): void
    {
        $this->productId           = null;
        $this->selectedProductName = null;
        $this->productSearch       = '';
        $this->productResults      = [];
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->productId           = null;
        $this->selectedProductName = null;
        $this->warehouseId         = null;
        $this->filterType          = '';
        $this->filterDirection     = '';
        $this->dateFrom            = '';
        $this->dateTo              = '';
        $this->productSearch       = '';
        $this->productResults      = [];
        $this->resetPage();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $query = PepsKardex::with(['product', 'warehouse', 'lot'])
            ->where('company_id', $companyId)
            ->when($this->productId, fn($q) => $q->where('product_id', $this->productId))
            ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
            ->when($this->filterType, fn($q) => $q->where('movement_type', $this->filterType))
            ->when($this->filterDirection, fn($q) => $q->where('direction', $this->filterDirection))
            ->when($this->dateFrom, fn($q) => $q->whereDate('moved_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('moved_at', '<=', $this->dateTo))
            ->orderBy('moved_at', 'desc')
            ->orderBy('id', 'desc');

        // Totales del conjunto filtrado (sin paginación ni ordenamiento)
        $totals = (clone $query)
            ->reorder()
            ->selectRaw('
                SUM(CASE WHEN direction="in"  THEN quantity     ELSE 0 END) as total_in_qty,
                SUM(CASE WHEN direction="out" THEN quantity     ELSE 0 END) as total_out_qty,
                SUM(CASE WHEN direction="in"  THEN total_cost   ELSE 0 END) as total_in_value,
                SUM(CASE WHEN direction="out" THEN total_cost   ELSE 0 END) as total_out_value,
                SUM(IFNULL(total_revenue, 0))                               as total_revenue,
                SUM(IFNULL(profit, 0))                                      as total_profit
            ')->first();

        $entries = $query->paginate(30);

        return view('livewire.inventory.peps-kardex', [
            'entries'    => $entries,
            'totals'     => $totals,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'types'      => PepsKardex::MOVEMENT_TYPES,
        ]);
    }
}
