<?php

namespace App\Livewire\Sales;

use App\Models\PriceList;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PriceListForm extends Component
{
    public ?PriceList $priceList = null;
    public string $name = '';
    public string $currency = 'MXN';
    public bool $is_default = false;
    public bool $is_active = true;
    public string $valid_from = '';
    public string $valid_to = '';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];

    public function mount($priceList = null): void
    {
        if ($priceList) {
            $this->priceList  = $priceList instanceof PriceList
                ? $priceList
                : PriceList::with('items.product')->findOrFail($priceList);
            $this->name       = $this->priceList->name;
            $this->currency   = $this->priceList->currency;
            $this->is_default = $this->priceList->is_default;
            $this->is_active  = $this->priceList->is_active;
            $this->valid_from = $this->priceList->valid_from?->format('Y-m-d') ?? '';
            $this->valid_to   = $this->priceList->valid_to?->format('Y-m-d') ?? '';
            $this->items      = $this->priceList->items->map(fn($i) => [
                'id'           => $i->id,
                'product_id'   => $i->product_id,
                'product_name' => $i->product->name,
                'price'        => $i->price,
                'discount_pct' => $i->discount_pct,
            ])->toArray();
        }
    }

    public function updatedProductSearch(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->productResults = [];
            return;
        }

        $existingIds = collect($this->items)->pluck('product_id')->filter()->toArray();

        $this->productResults = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->whereNotIn('id', $existingIds)
            ->where(fn($q) => $q
                ->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->limit(6)
            ->get(['id', 'name', 'sku', 'sale_price'])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $this->items[] = [
            'id'           => null,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'price'        => $product->sale_price,
            'discount_pct' => 0,
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'currency'              => 'required|in:MXN,USD',
            'is_default'            => 'boolean',
            'is_active'             => 'boolean',
            'valid_from'            => 'nullable|date',
            'valid_to'              => 'nullable|date|after_or_equal:valid_from',
            'items.*.price'         => 'required|numeric|min:0',
            'items.*.discount_pct'  => 'required|numeric|min:0|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'  => auth()->user()->company_id,
            'name'        => $this->name,
            'currency'    => $this->currency,
            'is_default'  => $this->is_default,
            'is_active'   => $this->is_active,
            'valid_from'  => $this->valid_from ?: null,
            'valid_to'    => $this->valid_to ?: null,
        ];

        if ($this->priceList?->exists) {
            $this->priceList->update($data);
            $priceList = $this->priceList;
        } else {
            $priceList = PriceList::create($data);
        }

        $priceList->items()->delete();
        foreach ($this->items as $item) {
            $priceList->items()->create([
                'product_id'   => $item['product_id'],
                'price'        => $item['price'],
                'discount_pct' => $item['discount_pct'],
            ]);
        }

        session()->flash('success', $this->priceList?->exists ? 'Lista actualizada.' : 'Lista creada.');
        $this->redirect(route('sales.price-lists.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.price-list-form');
    }
}