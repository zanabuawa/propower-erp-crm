<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class OrderForm extends Component
{
    public ?int $quotation_id = null;
    public ?int $customer_id = null;
    public ?int $price_list_id = null;
    public string $currency = 'MXN';
    public string $payment_method = 'cash';
    public string $payment_terms = '0';
    public string $global_discount = '0';
    public string $notes = '';
    public string $required_at = '';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];

    public function mount(): void
    {
        $this->required_at = now()->addDays(3)->format('Y-m-d');

        if (request()->has('quotation')) {
            $quotation = SaleQuotation::with('items.product')->find(request('quotation'));
            if ($quotation) {
                $this->quotation_id    = $quotation->id;
                $this->customer_id     = $quotation->customer_id;
                $this->price_list_id   = $quotation->price_list_id;
                $this->currency        = $quotation->currency;
                $this->items = $quotation->items->map(function ($i) {
                    $price        = (float) $i->unit_price;
                    $minSalePrice = $i->product
                        ? round((float)$i->product->purchase_price * (1 + (float)$i->product->operational_costs / 100), 2)
                        : 0;
                    $maxDiscPct   = ($price > 0 && $minSalePrice > 0)
                        ? round(max(0, ($price - $minSalePrice) / $price * 100), 4)
                        : 100;
                    return [
                        'product_id'       => $i->product_id,
                        'description'      => $i->description,
                        'quantity'         => $i->quantity,
                        'unit_price'       => $price,
                        'discount_pct'     => $i->discount_pct,
                        'tax_rate'         => $i->tax_rate,
                        'unit'             => $i->unit ?? '',
                        'min_sale_price'   => $minSalePrice,
                        'max_discount_pct' => $maxDiscPct,
                    ];
                })->toArray();
            }
        }

        if (empty($this->items)) {
            $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16, 'unit' => '', 'min_sale_price' => 0, 'max_discount_pct' => 100]];
        }
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
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->orWhere('barcode', 'like', "%{$this->productSearch}%"))
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'sale_price'])
            ->toArray();
    }

    #[On('product-picked')]
    public function productPicked(int $productId): void
    {
        $this->addProduct($productId);
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

        $cost           = (float) $product->purchase_price;
        $minSalePrice   = round($cost * (1 + (float)$product->operational_costs / 100), 2);
        $maxDiscountPct = $price > 0
            ? round(max(0, ($price - $minSalePrice) / $price * 100), 4)
            : 0;

        $this->items[] = [
            'product_id'       => $product->id,
            'description'      => $product->name,
            'quantity'         => 1,
            'unit_price'       => $price,
            'discount_pct'     => 0,
            'tax_rate'         => 16,
            'unit'             => '',
            'min_sale_price'   => $minSalePrice,
            'max_discount_pct' => $maxDiscountPct,
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16, 'unit' => '', 'min_sale_price' => 0, 'max_discount_pct' => 100];
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
        $itemDiscount  = collect($this->items)->sum(fn($i) =>
            ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (($i['discount_pct'] ?? 0) / 100)
        );
        $globalDiscount = $this->subtotal * ((float)$this->global_discount / 100);
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
            'payment_method'       => 'required|in:cash,transfer,card,check,credit',
            'payment_terms'        => 'required|integer|min:0',
            'global_discount'      => 'required|numeric|min:0|max:100',
            'required_at'          => 'required|date',
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
            $folio = 'OV-' . str_pad(
                SaleOrder::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $order = SaleOrder::create([
                'company_id'        => auth()->user()->company_id,
                'branch_id'         => auth()->user()->branch_id,
                'customer_id'       => $this->customer_id,
                'sale_quotation_id' => $this->quotation_id,
                'price_list_id'     => $this->price_list_id,
                'created_by'        => auth()->id(),
                'folio'             => $folio,
                'currency'          => $this->currency,
                'status'            => 'confirmed',
                'payment_method'    => $this->payment_method,
                'payment_terms'     => $this->payment_terms,
                'subtotal'          => $this->subtotal,
                'discount_amount'   => $this->discount,
                'tax'               => $this->tax,
                'total'             => $this->total,
                'notes'             => $this->notes,
                'required_at'       => $this->required_at,
            ]);

            foreach ($this->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemSubtotal * ($item['discount_pct'] / 100);
                $order->items()->create([
                    'product_id'      => $item['product_id'],
                    'description'     => $item['description'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_pct'    => $item['discount_pct'],
                    'discount_amount' => $itemDiscount,
                    'tax_rate'        => $item['tax_rate'],
                    'subtotal'        => $itemSubtotal - $itemDiscount,
                    'unit'            => $item['unit'] ?: null,
                ]);
            }

            // Marcar cotización como aceptada si viene de una
            if ($this->quotation_id) {
                SaleQuotation::find($this->quotation_id)?->update(['status' => 'accepted']);
            }
        });

        session()->flash('success', 'Orden de venta creada correctamente.');
        $this->redirect(route('sales.orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.order-form', [
            'customers'  => Customer::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
            'priceLists' => PriceList::where('company_id', auth()->user()->company_id)
                ->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}