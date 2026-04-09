<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class LotIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public ?int $filterWarehouse = null;

    #[Url]
    public ?int $filterProduct = null;

    public string $productSearch = '';
    public array $productResults = [];

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterWarehouse(): void  { $this->resetPage(); }
    public function updatedFilterProduct(): void  { $this->resetPage(); }

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
        $this->filterProduct = $id;
        $this->productSearch = $name;
        $this->productResults = [];
        $this->resetPage();
    }

    public function clearProductFilter(): void
    {
        $this->filterProduct = null;
        $this->productSearch = '';
        $this->productResults = [];
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search         = '';
        $this->filterStatus   = '';
        $this->filterWarehouse = null;
        $this->filterProduct  = null;
        $this->productSearch  = '';
        $this->productResults = [];
        $this->resetPage();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $lots = ProductLot::with(['product', 'warehouse'])
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(fn($q2) => $q2
                ->where('lot_number', 'like', "%{$this->search}%")
                ->orWhere('barcode', 'like', "%{$this->search}%")
                ->orWhere('reference', 'like', "%{$this->search}%")))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterWarehouse, fn($q) => $q->where('warehouse_id', $this->filterWarehouse))
            ->when($this->filterProduct, fn($q) => $q->where('product_id', $this->filterProduct))
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('livewire.inventory.lot-index', [
            'lots'       => $lots,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'statuses'   => ProductLot::STATUSES,
        ]);
    }
}
