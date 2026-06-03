<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\DiscountApproval;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\DiscountApprovalNotification;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesDiscountTier;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class OrderForm extends Component
{
    use HandlesDiscountTier;

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

    public string $approvalNotes   = '';
    public bool   $needsApproval   = false;
    public float  $exceedingMaxPct = 0;

    public function getSourceLabelProperty(): ?string
    {
        if (!$this->quotation_id) return null;
        return SaleQuotation::find($this->quotation_id)?->folio;
    }

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
        $this->required_at = now()->addDays(3)->format('Y-m-d');

        if (request()->has('quotation')) {
            $quotation = SaleQuotation::with('items.product')->find(request('quotation'));
            if ($quotation) {
                $this->quotation_id    = $quotation->id;
                $this->customer_id     = $quotation->customer_id;
                $this->price_list_id   = $quotation->price_list_id;
                $this->currency        = $quotation->currency;
                $this->items = $quotation->items->map(function ($i) {
                    $price   = (float) $i->unit_price;
                    $fields  = $i->product
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
        // Descuento global aplicado sobre el neto post-descuento de línea
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

    public function save(bool $forceApproval = false): void
    {
        $this->validate();

        // Bloqueo duro: nunca vender por debajo de costo + gastos operativos
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
            $folio = 'OV-' . str_pad(
                SaleOrder::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $approvalStatus = $exceedsLimit ? 'pending' : null;

            $order = SaleOrder::create([
                'company_id'        => $companyId,
                'branch_id'         => auth()->user()->branch_id,
                'customer_id'       => $this->customer_id,
                'sale_quotation_id' => $this->quotation_id,
                'price_list_id'     => $this->price_list_id,
                'created_by'        => auth()->id(),
                'folio'             => $folio,
                'currency'          => $this->currency,
                'status'            => 'confirmed',
                'approval_status'   => $approvalStatus,
                'payment_method'    => $this->payment_method,
                'payment_terms'     => $this->payment_terms,
                'subtotal'            => $this->subtotal,
                'global_discount_pct' => (float) $this->global_discount,
                'discount_amount'     => $this->discount,
                'tax'                 => $this->tax,
                'total'               => $this->total,
                'notes'             => $this->notes,
                'required_at'       => $this->required_at,
            ]);

            foreach ($this->items as $item) {
                $base    = $item['quantity'] * $item['unit_price'];
                $discAmt = $base * ($item['discount_pct'] / 100);
                $baseNet = $base - $discAmt;
                $taxAmt  = $baseNet * ($item['tax_rate'] / 100);

                $order->items()->create([
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

            // Comprometer stock
            $defaultWarehouseId = auth()->user()->branch_id
                ? Warehouse::where('branch_id', auth()->user()->branch_id)
                    ->where('is_active', true)->where('is_transit', false)
                    ->where('is_defective', false)->first()?->id
                : null;

            if ($defaultWarehouseId) {
                foreach ($this->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $defaultWarehouseId],
                        ['quantity' => 0, 'committed_quantity' => 0]
                    );
                    $stock->commit((float) $item['quantity']);
                }
            }

            // Marcar cotización como aceptada si viene de una
            if ($this->quotation_id) {
                SaleQuotation::find($this->quotation_id)?->update(['status' => 'accepted']);
            }

            if ($exceedsLimit) {
                $approval = DiscountApproval::create([
                    'company_id'             => $companyId,
                    'requester_id'           => auth()->id(),
                    'model_type'             => SaleOrder::class,
                    'model_id'               => $order->id,
                    'status'                 => 'pending',
                    'requested_discount_pct' => $limitCheck['requested'],
                    'max_allowed_pct'        => $limitCheck['max_allowed'],
                    'requester_notes'        => $this->approvalNotes ?: null,
                ]);
                $order->update(['approval_id' => $approval->id]);

                User::where('company_id', $companyId)
                    ->permission('approve discounts')
                    ->get()
                    ->each(fn($u) => $u->notify(new DiscountApprovalNotification(
                        type:          'requested',
                        folio:         $order->folio,
                        requestedPct:  $limitCheck['requested'],
                        maxAllowedPct: $limitCheck['max_allowed'],
                        notes:         $this->approvalNotes ?: null,
                        approvalId:    $approval->id,
                    )));
            }
        });

        $this->needsApproval = false;
        session()->flash('success', $exceedsLimit
            ? 'Orden guardada y enviada a autorización de descuento.'
            : 'Orden de venta creada correctamente.');
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
