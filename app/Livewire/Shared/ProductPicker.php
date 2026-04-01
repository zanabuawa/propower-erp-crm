<?php

namespace App\Livewire\Shared;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;

class ProductPicker extends Component
{
    public bool   $isOpen     = false;
    public string $search     = '';
    public ?int   $categoryId = null;
    public ?int   $supplierId = null;

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->reset(['search', 'categoryId', 'supplierId']);
    }

    public function pick(int $productId): void
    {
        $this->dispatch('product-picked', productId: $productId);
        $this->close();
    }

    public function render()
    {
        $products = collect();

        if ($this->isOpen) {
            $products = Product::query()
                ->where('company_id', auth()->user()->company_id)
                ->where('is_active', true)
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%"))
                ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
                ->when($this->supplierId, fn($q) => $q->where('supplier_id', $this->supplierId))
                ->with(['category', 'primaryImage', 'stocks'])
                ->orderBy('name')
                ->limit(60)
                ->get();
        }

        return view('livewire.shared.product-picker', [
            'products'   => $products,
            'categories' => $this->isOpen
                ? Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get()
                : collect(),
            'suppliers'  => $this->isOpen
                ? Supplier::where('company_id', auth()->user()->company_id)
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : collect(),
        ]);
    }
}
