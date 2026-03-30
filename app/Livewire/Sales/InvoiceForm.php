<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InvoiceForm extends Component
{
    public ?int $order_id = null;
    public ?int $customer_id = null;
    public string $type = 'internal';
    public string $currency = 'MXN';
    public string $payment_method = 'cash';
    public string $payment_terms = '0';
    public string $global_discount = '0';
    public string $notes = '';
    public string $issued_at = '';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];

    public function mount(): void
    {
        $this->issued_at = now()->format('Y-m-d');

        if (request()->has('order')) {
            $order = SaleOrder::with('items.product')->find(request('order'));
            if ($order) {
                $this->order_id      = $order->id;
                $this->customer_id   = $order->customer_id;
                $this->currency      = $order->currency;
                $this->payment_method = $order->payment_method;
                $this->payment_terms  = $order->payment_terms;
                $this->items          = $order->items->map(fn($i) => [
                    'product_id'   => $i->product_id,
                    'description'  => $i->description,
                    'quantity'     => $i->quantity,
                    'unit_price'   => $i->unit_price,
                    'discount_pct' => $i->discount_pct,
                    'tax_rate'     => $i->tax_rate,
                    'unit'         => $i->unit ?? '',
                ])->toArray();
            }
        }

        if (empty($this->items)) {
            $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16, 'unit' => '']];
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

        $this->items[] = [
            'product_id'   => $product->id,
            'description'  => $product->name,
            'quantity'     => 1,
            'unit_price'   => $product->sale_price,
            'discount_pct' => 0,
            'tax_rate'     => 16,
            'unit'         => '',
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16, 'unit' => ''];
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
        $itemDiscount   = collect($this->items)->sum(fn($i) =>
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
            'type'                 => 'required|in:internal,cfdi',
            'currency'             => 'required|in:MXN,USD',
            'payment_method'       => 'required|in:cash,transfer,card,check,credit',
            'payment_terms'        => 'required|integer|min:0',
            'global_discount'      => 'required|numeric|min:0|max:100',
            'issued_at'            => 'required|date',
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

        DB::transaction(function () {
            $folio = 'FAC-' . str_pad(
                SaleInvoice::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $dueAt = null;
            if ((int)$this->payment_terms > 0) {
                $dueAt = now()->addDays((int)$this->payment_terms);
            }

            $invoice = SaleInvoice::create([
                'company_id'      => auth()->user()->company_id,
                'sale_order_id'   => $this->order_id,
                'customer_id'     => $this->customer_id,
                'created_by'      => auth()->id(),
                'folio'           => $folio,
                'type'            => $this->type,
                'currency'        => $this->currency,
                'status'          => 'draft',
                'payment_method'  => $this->payment_method,
                'subtotal'        => $this->subtotal,
                'discount_amount' => $this->discount,
                'tax'             => $this->tax,
                'total'           => $this->total,
                'paid_amount'     => 0,
                'notes'           => $this->notes,
                'issued_at'       => $this->issued_at,
                'due_at'          => $dueAt,
            ]);

            foreach ($this->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemSubtotal * ($item['discount_pct'] / 100);
                $invoice->items()->create([
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

            if ($this->order_id) {
                SaleOrder::find($this->order_id)?->update(['status' => 'invoiced']);
            }
        });

        session()->flash('success', 'Factura creada correctamente.');
        $this->redirect(route('sales.invoices.index'));
    }

    public function render()
    {
        return view('livewire.sales.invoice-form', [
            'customers' => Customer::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}