<?php

namespace App\Livewire\Shared;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ProductPicker extends Component
{
    public bool   $isOpen     = false;
    public string $search     = '';
    public ?int   $categoryId = null;
    public ?int   $supplierId = null;
    public bool   $multiSelect = false;
    public array  $selectedIds = [];

    /** When set, only stockable products with stock > 0 in this warehouse are shown
     *  and stock quantity is displayed instead of price. */
    #[Reactive]
    public ?int $warehouseId = null;

    public function mount(bool $multiSelect = false): void
    {
        $this->multiSelect = $multiSelect;
    }

    public function open(): void
    {
        $this->selectedIds = [];
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->selectedIds = [];
        $this->reset(['search', 'categoryId', 'supplierId']);
    }

    public function pick(int $productId): void
    {
        if ($this->multiSelect) {
            if (in_array($productId, $this->selectedIds)) {
                $this->selectedIds = array_values(array_filter($this->selectedIds, fn($id) => $id !== $productId));
            } else {
                $this->selectedIds[] = $productId;
            }
        } else {
            $this->dispatch('product-picked', productId: $productId);
            $this->close();
        }
    }

    public function confirm(): void
    {
        if (!empty($this->selectedIds)) {
            $this->dispatch('products-picked', productIds: $this->selectedIds);
        }
        $this->close();
    }

    public function render()
    {
        $products  = collect();
        $warehouse = null;

        if ($this->isOpen) {
            $query = Product::query()
                ->where('company_id', auth()->user()->company_id)
                ->where('is_active', true)
                ->when($this->search, fn($q) => $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('barcode', 'like', "%{$this->search}%");
                }))
                ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
                ->when(!$this->warehouseId, fn($q) => $q  // supplier filter only in standard mode
                    ->when($this->supplierId, fn($q) => $q->where('supplier_id', $this->supplierId)))
                ->with(['category', 'primaryImage']);

            if ($this->warehouseId) {
                // Warehouse mode: filter to products that have stock in this warehouse
                $query->whereHas('stocks', fn($q) => $q
                    ->where('warehouse_id', $this->warehouseId)
                    ->where('quantity', '>', 0))
                    ->with(['stocks' => fn($q) => $q->where('warehouse_id', $this->warehouseId)]);

                $warehouse = Warehouse::find($this->warehouseId);
            } else {
                $query->with(['stocks']);
            }

            $products = $query->orderBy('name')->limit(60)->get();
        }

        return view('livewire.shared.product-picker', [
            'products'      => $products,
            'warehouse'     => $warehouse,
            'categories'    => $this->isOpen
                ? Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get()
                : collect(),
            'suppliers'     => ($this->isOpen && !$this->warehouseId)
                ? Supplier::where('company_id', auth()->user()->company_id)
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : collect(),
        ]);
    }
}
