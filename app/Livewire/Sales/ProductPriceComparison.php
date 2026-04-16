<?php

namespace App\Livewire\Sales;

use App\Models\PriceList;
use App\Models\PriceListItemHistory;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class ProductPriceComparison extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'products';

    // ── Tab: Por producto ─────────────────────────────────────────────────────
    public string $searchProduct = '';

    // ── Tab: Comparador ───────────────────────────────────────────────────────
    public string $compareSearchA = '';
    public string $compareSearchB = '';
    public array  $compareResultsA = [];
    public array  $compareResultsB = [];
    public ?int   $compareProductA = null;
    public ?int   $compareProductB = null;
    public ?string $compareNameA   = null;
    public ?string $compareNameB   = null;

    // ── Tab: Historial ────────────────────────────────────────────────────────
    public string $historySearch        = '';
    public array  $historyResults       = [];
    public ?int   $historyProductId     = null;
    public ?string $historyProductName  = null;
    public ?int   $historyPriceListId   = null;
    public string $historyDateFrom      = '';
    public string $historyDateTo        = '';

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    // ── Búsqueda en tab productos ─────────────────────────────────────────────

    public function updatingSearchProduct(): void { $this->resetPage(); }

    // ── Comparador: autocomplete A y B ────────────────────────────────────────

    public function updatedCompareSearchA(): void
    {
        $this->compareResultsA = $this->searchProducts($this->compareSearchA);
    }

    public function updatedCompareSearchB(): void
    {
        $this->compareResultsB = $this->searchProducts($this->compareSearchB);
    }

    public function selectCompareA(int $id, string $name): void
    {
        $this->compareProductA  = $id;
        $this->compareNameA     = $name;
        $this->compareSearchA   = '';
        $this->compareResultsA  = [];
    }

    public function selectCompareB(int $id, string $name): void
    {
        $this->compareProductB  = $id;
        $this->compareNameB     = $name;
        $this->compareSearchB   = '';
        $this->compareResultsB  = [];
    }

    public function clearCompareA(): void
    {
        $this->compareProductA = null;
        $this->compareNameA    = null;
    }

    public function clearCompareB(): void
    {
        $this->compareProductB = null;
        $this->compareNameB    = null;
    }

    // ── Historial: autocomplete de producto ───────────────────────────────────

    public function updatedHistorySearch(): void
    {
        $this->historyResults = strlen($this->historySearch) >= 2
            ? $this->searchProducts($this->historySearch)
            : [];
    }

    public function selectHistoryProduct(int $id, string $name): void
    {
        $this->historyProductId   = $id;
        $this->historyProductName = $name;
        $this->historySearch      = '';
        $this->historyResults     = [];
        $this->historyPriceListId = null;
        $this->resetPage();
    }

    public function clearHistoryProduct(): void
    {
        $this->historyProductId   = null;
        $this->historyProductName = null;
        $this->historyPriceListId = null;
        $this->resetPage();
    }

    public function updatingHistoryPriceListId(): void { $this->resetPage(); }
    public function updatingHistoryDateFrom(): void     { $this->resetPage(); }
    public function updatingHistoryDateTo(): void       { $this->resetPage(); }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function searchProducts(string $term): array
    {
        if (strlen($term) < 2) return [];

        return Product::with('supplier')
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(fn($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'sku', 'supplier_id'])
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'sku'      => $p->sku,
                'supplier' => $p->supplier?->name,
            ])
            ->toArray();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $companyId  = auth()->user()->company_id;
        $priceLists = PriceList::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // ── Tab: Por producto ─────────────────────────────────────────────────
        $productsWithPrices = collect();
        if ($this->tab === 'products') {
            $query = Product::with([
                'priceListItems.priceList',
                'category',
                'supplier',
            ])
                ->where('company_id', $companyId)
                ->where('is_active', true);

            if ($this->searchProduct) {
                $query->where(fn($q) => $q
                    ->where('name', 'like', "%{$this->searchProduct}%")
                    ->orWhere('sku', 'like', "%{$this->searchProduct}%"));
            }

            $productsWithPrices = $query->orderBy('name')->paginate(25);
        }

        // ── Tab: Comparador ───────────────────────────────────────────────────
        $comparisonData = collect();
        if ($this->tab === 'compare' && ($this->compareProductA || $this->compareProductB)) {
            $ids = array_filter([$this->compareProductA, $this->compareProductB]);
            $comparisonData = Product::with(['priceListItems.priceList', 'supplier'])
                ->whereIn('id', $ids)
                ->get();
        }

        // ── Tab: Historial ────────────────────────────────────────────────────
        $historyEntries = collect();
        $historyListsForProduct = collect();
        if ($this->tab === 'history' && $this->historyProductId) {
            $hQuery = PriceListItemHistory::with(['priceList', 'changedBy'])
                ->where('product_id', $this->historyProductId);

            if ($this->historyPriceListId) {
                $hQuery->where('price_list_id', $this->historyPriceListId);
            }
            if ($this->historyDateFrom) {
                $hQuery->where('changed_at', '>=', $this->historyDateFrom);
            }
            if ($this->historyDateTo) {
                $hQuery->where('changed_at', '<=', $this->historyDateTo . ' 23:59:59');
            }

            $historyEntries = $hQuery->orderBy('changed_at', 'desc')->paginate(30);

            // Listas que tienen historial de este producto
            $historyListsForProduct = PriceListItemHistory::where('product_id', $this->historyProductId)
                ->distinct('price_list_id')
                ->with('priceList')
                ->get()
                ->pluck('priceList')
                ->filter()
                ->unique('id');
        }

        return view('livewire.sales.product-price-comparison', compact(
            'priceLists',
            'productsWithPrices',
            'comparisonData',
            'historyEntries',
            'historyListsForProduct',
        ));
    }
}
