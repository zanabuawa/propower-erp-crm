<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InventoryGeneral extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $category_id = '';
    public string $stock_filter = ''; // 'low', 'out', 'ok'

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingCategoryId(): void { $this->resetPage(); }
    public function updatingStockFilter(): void { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $query = Product::with(['category', 'unitOfMeasure', 'stocks'])
            ->where('company_id', $companyId)
            ->where('is_active', true);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        // We fetch all to filter by computed stock, then paginate manually via collection
        // For large catalogs this should be a DB sum subquery; kept simple for now.
        $products = $query->orderBy('name')->get()->map(function (Product $p) {
            $p->total_qty = $p->stocks->sum('quantity');
            return $p;
        });

        if ($this->stock_filter === 'out') {
            $products = $products->filter(fn($p) => $p->total_qty <= 0);
        } elseif ($this->stock_filter === 'low') {
            $products = $products->filter(fn($p) => $p->total_qty > 0 && $p->total_qty <= $p->min_stock);
        } elseif ($this->stock_filter === 'ok') {
            $products = $products->filter(fn($p) => $p->total_qty > $p->min_stock);
        }

        // Summary totals
        $totalProducts  = $products->count();
        $outOfStock     = $products->filter(fn($p) => $p->total_qty <= 0)->count();
        $lowStock       = $products->filter(fn($p) => $p->total_qty > 0 && $p->total_qty <= $p->min_stock)->count();
        $totalStockValue = $products->sum(fn($p) => $p->total_qty * (float) $p->purchase_price);

        return view('livewire.inventory.inventory-general', [
            'products'        => $products->values(),
            'categories'      => Category::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'totalProducts'   => $totalProducts,
            'outOfStock'      => $outOfStock,
            'lowStock'        => $lowStock,
            'totalStockValue' => $totalStockValue,
        ]);
    }
}
