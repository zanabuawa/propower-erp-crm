<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Stock;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InventoryByWarehouse extends Component
{
    public ?int    $warehouse_id  = null;
    public string  $search        = '';
    public string  $category_id   = '';
    public string  $stock_filter  = '';

    public function mount(): void
    {
        // Default to first warehouse of the company
        $first = Warehouse::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->first();
        $this->warehouse_id = $first?->id;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $warehouses = Warehouse::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stocks = collect();
        $selectedWarehouse = null;

        if ($this->warehouse_id) {
            $selectedWarehouse = $warehouses->firstWhere('id', $this->warehouse_id);

            $query = Stock::with(['product.category', 'product.unitOfMeasure'])
                ->where('warehouse_id', $this->warehouse_id)
                ->whereHas('product', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)->where('is_active', true);
                });

            if ($this->search) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->category_id) {
                $query->whereHas('product', fn($q) => $q->where('category_id', $this->category_id));
            }

            $stocks = $query->get();

            if ($this->stock_filter === 'out') {
                $stocks = $stocks->filter(fn($s) => $s->quantity <= 0);
            } elseif ($this->stock_filter === 'low') {
                $stocks = $stocks->filter(fn($s) => $s->quantity > 0 && $s->quantity <= $s->product->min_stock);
            } elseif ($this->stock_filter === 'ok') {
                $stocks = $stocks->filter(fn($s) => $s->quantity > $s->product->min_stock);
            }
        }

        $totalValue = $stocks->sum(fn($s) => $s->quantity * (float) $s->product->purchase_price);
        $outCount   = $stocks->filter(fn($s) => $s->quantity <= 0)->count();
        $lowCount   = $stocks->filter(fn($s) => $s->quantity > 0 && $s->quantity <= $s->product->min_stock)->count();

        return view('livewire.inventory.inventory-by-warehouse', [
            'warehouses'        => $warehouses,
            'selectedWarehouse' => $selectedWarehouse,
            'stocks'            => $stocks->values(),
            'categories'        => Category::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'totalValue'        => $totalValue,
            'outCount'          => $outCount,
            'lowCount'          => $lowCount,
        ]);
    }
}
