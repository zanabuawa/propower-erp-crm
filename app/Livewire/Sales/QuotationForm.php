<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\SaleQuotation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class QuotationForm extends Component
{
    public ?int $customer_id = null;
    public ?int $price_list_id = null;
    public string $currency = 'MXN';
    public string $valid_days = '15';
    public string $notes = '';
    public string $terms = '';
    public string $global_discount = '0';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];

    public function mount(): void
    {
        $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16, 'unit' => '', 'notes' => '', 'min_sale_price' => 0, 'max_discount_pct' => 100]];
    }

    public function updatedCustomerId(): void
    {
        if ($this->customer_id) {
            $customer = Customer::with('priceLists')->find($this->customer_id);
            $priceList = $customer?->priceLists->first();
            if ($priceList) {
                $this->price_list_id = $priceList->id;
                $this->currency = $priceList->currency;
            }
        }
    }

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
            ->limit(6)
            ->get(['id', 'name', 'sku', 'sale_price'])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $price = (float) $product->sale_price;

        if ($this->price_list_id) {
            $priceList = PriceList::with('items')->find($this->price_list_id);
            $listPrice = $priceList?->getPriceForProduct($productId);
            if ($listPrice) $price = $listPrice;
        }

        // Precio mínimo = costo * (1 + gastos_op% / 100)
        $cost         = (float) $product->purchase_price;
        $minSalePrice = round($cost * (1 + (float)$product->operational_costs / 100), 2);
        // Descuento máximo en % sobre el precio de venta normal
        $maxDiscountPct = $price > 0
            ? round(max(0, ($price - $minSalePrice) / $price * 100), 4)
            : 0;

        $this->items[] = [
            'product_id'      => $product->id,
            'description'     => $product->name,
            'quantity'        => 1,
            'unit_price'      => $price,
            'discount_pct'    => 0,
            'tax_rate'        => 16,
            'unit'            => '',
            'notes'           => '',
            'min_sale_price'  => $minSalePrice,
            'max_discount_pct' => $maxDiscountPct,
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16, 'unit' => '', 'notes' => '', 'min_sale_price' => 0, 'max_discount_pct' => 100];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
    }

    public function getDiscountProperty(): float
    {
        $itemDiscount = collect($this->items)->sum(
            fn($i) =>
            ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (($i['discount_pct'] ?? 0) / 100)
        );
        $globalDiscount = $this->subtotal * ((float) $this->global_discount / 100);
        return $itemDiscount + $globalDiscount;
    }

    public function getTaxProperty(): float
    {
        return collect($this->items)->sum(function ($i) {
            $base = ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (1 - ($i['discount_pct'] ?? 0) / 100);
            return $base * (($i['tax_rate'] ?? 0) / 100);
        });
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal - $this->discount + $this->tax;
    }

    public function rules(): array
    {
        return [
            'customer_id'          => 'required|exists:customers,id',
            'currency'             => 'required|in:MXN,USD',
            'valid_days'           => 'required|integer|min:1',
            'global_discount'      => 'required|numeric|min:0|max:100',
            'items'                => 'required|array|min:1',
            'items.*.description'  => 'required|string|max:255',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.discount_pct' => 'required|numeric|min:0|max:100',
            'items.*.tax_rate'     => 'required|numeric|min:0|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Validar que el precio con descuento no sea menor al precio mínimo (costo + gastos op.)
        foreach ($this->items as $index => $item) {
            $minPrice   = (float) ($item['min_sale_price'] ?? 0);
            $unitPrice  = (float) ($item['unit_price'] ?? 0);
            $discPct    = (float) ($item['discount_pct'] ?? 0);
            $finalPrice = round($unitPrice * (1 - $discPct / 100), 2);

            if ($minPrice > 0 && $finalPrice < $minPrice) {
                $maxPct = round(max(0, ($unitPrice - $minPrice) / $unitPrice * 100), 2);
                $this->addError("items.{$index}.discount_pct",
                    "Descuento excede el máximo permitido ({$maxPct}%). Precio mínimo: \${$minPrice}."
                );
                return;
            }
        }

        DB::transaction(function () {
            $folio = 'COT-' . str_pad(
                SaleQuotation::where('company_id', auth()->user()->company_id)->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $subtotal = $this->subtotal;
            $discount = $this->discount;
            $tax = $this->tax;
            $total = $this->total;

            $quotation = SaleQuotation::create([
                'company_id' => auth()->user()->company_id,
                'branch_id' => auth()->user()->branch_id,
                'customer_id' => $this->customer_id,
                'price_list_id' => $this->price_list_id,
                'created_by' => auth()->id(),
                'folio' => $folio,
                'currency' => $this->currency,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'valid_days' => $this->valid_days,
                'valid_until' => now()->addDays((int) $this->valid_days),
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);

            foreach ($this->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemSubtotal * ($item['discount_pct'] / 100);
                $quotation->items()->create([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_pct' => $item['discount_pct'],
                    'discount_amount' => $itemDiscount,
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $itemSubtotal - $itemDiscount,
                    'unit' => $item['unit'] ?: null,
                    'notes' => $item['notes'] ?: null,
                ]);
            }
        });

        session()->flash('success', 'Cotización creada correctamente.');
        $this->redirect(route('sales.index'));
    }

    public function render()
    {
        return view('livewire.sales.quotation-form', [
            'customers' => Customer::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')
                ->orderBy('name')->get(),
            'priceLists' => PriceList::where('company_id', auth()->user()->company_id)
                ->where('is_active', true)
                ->orderBy('name')->get(),
        ]);
    }
}