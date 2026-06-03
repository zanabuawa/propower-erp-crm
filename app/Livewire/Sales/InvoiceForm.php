<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\DiscountApproval;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleOrder;
use App\Models\User;
use App\Notifications\DiscountApprovalNotification;
use App\Traits\HandlesDiscountTier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class InvoiceForm extends Component
{
    use HandlesDiscountTier;

    public ?int $order_id = null;
    public ?int $customer_id = null;
    public ?int $price_list_id = null;
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

    public string $approvalNotes   = '';
    public bool   $needsApproval   = false;
    public float  $exceedingMaxPct = 0;

    private static function blankItem(): array
    {
        return [
            'product_id'       => null,
            'description'      => '',
            'quantity'         => 1,
            'unit_price'       => 0,
            'discount_pct'     => 0,
            'tax_rate'         => 16,
            'unit'             => '',
            'min_sale_price'   => 0,
            'max_discount_pct' => 100,
        ];
    }

    public function mount(): void
    {
        $this->issued_at = now()->format('Y-m-d');

        if (request()->has('order')) {
            $order = SaleOrder::with('items.product')->find(request('order'));
            if ($order) {
                $this->order_id       = $order->id;
                $this->customer_id    = $order->customer_id;
                $this->price_list_id  = $order->price_list_id;
                $this->currency       = $order->currency;
                $this->payment_method = $order->payment_method;
                $this->payment_terms  = $order->payment_terms;
                $this->global_discount = (string) $order->global_discount_pct;

                $this->items = $order->items->map(function ($i) {
                    $price  = (float) $i->unit_price;
                    $fields = $i->product
                        ? $this->resolveItemDiscountFields(
                            $price,
                            (float) $i->product->purchase_price,
                            (float) $i->product->operational_costs
                          )
                        : ['min_sale_price' => 0, 'max_discount_pct' => 100];

                    return [
                        'product_id'       => $i->product_id,
                        'description'      => $i->description,
                        'quantity'         => $i->quantity,
                        'unit_price'       => $price,
                        'discount_pct'     => $i->discount_pct,
                        'tax_rate'         => $i->tax_rate,
                        'unit'             => $i->unit ?? '',
                        'min_sale_price'   => $fields['min_sale_price'],
                        'max_discount_pct' => $fields['max_discount_pct'],
                    ];
                })->toArray();
            }
        }

        if (empty($this->items)) {
            $this->items = [self::blankItem()];
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

    #[On('products-picked')]
    public function productsPicked(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->addProduct((int) $productId);
        }
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

        $fields = $this->resolveItemDiscountFields(
            $price,
            (float) $product->purchase_price,
            (float) $product->operational_costs
        );

        $this->items[] = [
            'product_id'       => $product->id,
            'description'      => $product->name,
            'quantity'         => 1,
            'unit_price'       => $price,
            'discount_pct'     => 0,
            'tax_rate'         => 16,
            'unit'             => $product->unitOfMeasure?->abbreviation ?? '',
            'min_sale_price'   => $fields['min_sale_price'],
            'max_discount_pct' => $fields['max_discount_pct'],
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = self::blankItem();
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
        $itemDiscount = collect($this->items)->sum(fn($i) =>
            ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (($i['discount_pct'] ?? 0) / 100)
        );
        $netSubtotal    = $this->subtotal - $itemDiscount;
        $globalDiscount = $netSubtotal * ((float) $this->global_discount / 100);
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
            'payment_terms'        => 'required|integer|min:0|max:3650',
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

    public function save(bool $forceApproval = false): void
    {
        $this->validate();

        if ($this->isAbsoluteFloorBreached()) {
            $this->addError('global_discount', 'El descuento excede el precio mínimo permitido (costo + gastos operativos). No es posible guardar.');
            return;
        }

        $limitCheck   = $this->checkDiscountLimits();
        $exceedsLimit = $limitCheck['exceeds'];

        if ($exceedsLimit && !$forceApproval) {
            $this->needsApproval = true;
            return;
        }

        DB::transaction(function () use ($exceedsLimit, $limitCheck) {
            $companyId = auth()->user()->company_id;
            $folio = 'FAC-' . str_pad(
                SaleInvoice::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $dueAt = (int) $this->payment_terms > 0
                ? now()->addDays((int) $this->payment_terms)
                : null;

            $invoice = SaleInvoice::create([
                'company_id'          => $companyId,
                'sale_order_id'       => $this->order_id,
                'customer_id'         => $this->customer_id,
                'created_by'          => auth()->id(),
                'folio'               => $folio,
                'type'                => $this->type,
                'currency'            => $this->currency,
                'status'              => 'draft',
                'payment_method'      => $this->payment_method,
                'subtotal'            => $this->subtotal,
                'global_discount_pct' => (float) $this->global_discount,
                'discount_amount'     => $this->discount,
                'tax'                 => $this->tax,
                'total'               => $this->total,
                'paid_amount'         => 0,
                'notes'               => $this->notes,
                'issued_at'           => $this->issued_at,
                'due_at'              => $dueAt,
            ]);

            foreach ($this->items as $item) {
                $base    = $item['quantity'] * $item['unit_price'];
                $discAmt = $base * ($item['discount_pct'] / 100);
                $baseNet = $base - $discAmt;
                $taxAmt  = $baseNet * ($item['tax_rate'] / 100);

                $invoice->items()->create([
                    'product_id'      => $item['product_id'],
                    'description'     => $item['description'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_pct'    => $item['discount_pct'],
                    'discount_amount' => $discAmt,
                    'tax_rate'        => $item['tax_rate'],
                    'subtotal'        => $baseNet + $taxAmt,
                    'unit'            => $item['unit'] ?: null,
                ]);
            }

            if ($this->order_id) {
                SaleOrder::find($this->order_id)?->update(['status' => 'invoiced']);
            }

            if ($exceedsLimit) {
                $approval = DiscountApproval::create([
                    'company_id'             => $companyId,
                    'requester_id'           => auth()->id(),
                    'model_type'             => SaleInvoice::class,
                    'model_id'               => $invoice->id,
                    'status'                 => 'pending',
                    'requested_discount_pct' => $limitCheck['requested'],
                    'max_allowed_pct'        => $limitCheck['max_allowed'],
                    'requester_notes'        => $this->approvalNotes ?: null,
                ]);
                $invoice->update(['approval_id' => $approval->id]);

                User::where('company_id', $companyId)
                    ->permission('approve discounts')
                    ->get()
                    ->each(fn($u) => $u->notify(new DiscountApprovalNotification(
                        type:          'requested',
                        folio:         $invoice->folio,
                        requestedPct:  $limitCheck['requested'],
                        maxAllowedPct: $limitCheck['max_allowed'],
                        notes:         $this->approvalNotes ?: null,
                        approvalId:    $approval->id,
                    )));
            }
        });

        $this->needsApproval = false;
        session()->flash('success', $exceedsLimit
            ? 'Factura guardada y enviada a autorización de descuento.'
            : 'Factura creada correctamente.');
        $this->redirect(route('sales.invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.invoice-form', [
            'customers' => Customer::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
