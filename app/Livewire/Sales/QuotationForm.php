<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\DiscountApproval;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\SaleQuotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

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
        $this->items = [self::blankItem()];
    }

    private static function blankItem(): array
    {
        return ['product_id' => null, 'description' => '', 'quantity' => 1,
                'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => 16,
                'ieps_rate' => 0, 'unit' => '', 'notes' => '',
                'min_sale_price' => 0, 'max_discount_pct' => 100];
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

        // Precio mínimo = costo * (1 + gastos_op% / 100)
        $cost         = (float) $product->purchase_price;
        $minSalePrice = round($cost * (1 + (float)$product->operational_costs / 100), 2);
        // Descuento máximo en % sobre el precio de venta normal
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
            'ieps_rate'        => (float) $product->ieps_rate,
            'unit'             => $product->unitOfMeasure?->abbreviation ?? '',
            'notes'            => '',
            'min_sale_price'   => $minSalePrice,
            'max_discount_pct' => $maxDiscountPct,
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = self::blankItem();
    }

    public function setAllIva(bool $add): void
    {
        foreach ($this->items as &$item) {
            $item['tax_rate'] = $add ? 16 : 0;
        }
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function toggleItemIva(int $index): void
    {
        if (!isset($this->items[$index])) return;
        // Alterna entre IVA incluido (0%) e IVA no incluido (16%)
        $this->items[$index]['tax_rate'] = ($this->items[$index]['tax_rate'] != 0) ? 0 : 16;
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

    public function getIepsProperty(): float
    {
        return collect($this->items)->sum(function ($i) {
            $base = ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (1 - ($i['discount_pct'] ?? 0) / 100);
            return $base * (($i['ieps_rate'] ?? 0) / 100);
        });
    }

    public function getTaxProperty(): float
    {
        // IVA se calcula sobre (base + IEPS)
        return collect($this->items)->sum(function ($i) {
            $base = ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (1 - ($i['discount_pct'] ?? 0) / 100);
            $ieps = $base * (($i['ieps_rate'] ?? 0) / 100);
            return ($base + $ieps) * (($i['tax_rate'] ?? 0) / 100);
        });
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal - $this->discount + $this->ieps + $this->tax;
    }

    public function getMaxGlobalDiscountProperty(): float
    {
        // El descuento global máximo es el mínimo de los máximos por línea
        if (empty($this->items)) return 100;
        return (float) collect($this->items)->min('max_discount_pct') ?? 100;
    }

    public string $approvalNotes  = '';
    public bool   $needsApproval  = false;
    public float  $exceedingMaxPct = 0;

    public function rules(): array
    {
        return [
            'customer_id'           => 'required|exists:customers,id',
            'currency'              => 'required|in:MXN,USD',
            'valid_days'            => 'required|integer|min:1',
            'global_discount'       => 'required|numeric|min:0|max:100',
            'items'                 => 'required|array|min:1',
            'items.*.description'   => 'required|string|max:255',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.discount_pct'  => 'required|numeric|min:0|max:100',
            'items.*.tax_rate'      => 'required|numeric|min:0|max:100',
            'items.*.ieps_rate'     => 'required|numeric|min:0|max:100',
        ];
    }

    /** Detecta si algún ítem supera su descuento máximo permitido. */
    private function checkDiscountLimits(): bool
    {
        foreach ($this->items as $index => $item) {
            $minPrice   = (float) ($item['min_sale_price'] ?? 0);
            $unitPrice  = (float) ($item['unit_price'] ?? 0);
            $discPct    = (float) ($item['discount_pct'] ?? 0);
            $maxPct     = (float) ($item['max_discount_pct'] ?? 100);

            if ($discPct > $maxPct) {
                $this->exceedingMaxPct = $maxPct;
                return true;
            }
            if ($minPrice > 0 && $unitPrice > 0) {
                $finalPrice = $unitPrice * (1 - $discPct / 100);
                if ($finalPrice < $minPrice) {
                    $this->exceedingMaxPct = round(max(0, ($unitPrice - $minPrice) / $unitPrice * 100), 2);
                    return true;
                }
            }
        }
        return false;
    }

    public function save(bool $forceApproval = false): void
    {
        $this->validate();

        $exceedsLimit = $this->checkDiscountLimits();

        if ($exceedsLimit && !$forceApproval) {
            // Mostrar modal de solicitud de autorización
            $this->needsApproval = true;
            return;
        }

        DB::transaction(function () use ($exceedsLimit) {
            $companyId = auth()->user()->company_id;
            $folio = 'COT-' . str_pad(
                SaleQuotation::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $approvalStatus = $exceedsLimit ? 'pending' : null;

            $quotation = SaleQuotation::create([
                'company_id'      => $companyId,
                'branch_id'       => auth()->user()->branch_id,
                'customer_id'     => $this->customer_id,
                'price_list_id'   => $this->price_list_id,
                'created_by'      => auth()->id(),
                'folio'           => $folio,
                'currency'        => $this->currency,
                'status'          => 'draft',
                'approval_status' => $approvalStatus,
                'subtotal'        => $this->subtotal,
                'discount_amount' => $this->discount,
                'ieps'            => $this->ieps,
                'tax'             => $this->tax,
                'total'           => $this->total,
                'valid_days'      => $this->valid_days,
                'valid_until'     => now()->addDays((int) $this->valid_days),
                'notes'           => $this->notes,
                'terms'           => $this->terms,
            ]);

            foreach ($this->items as $item) {
                $base        = $item['quantity'] * $item['unit_price'];
                $discAmt     = $base * ($item['discount_pct'] / 100);
                $baseNet     = $base - $discAmt;
                $iepsAmt     = $baseNet * ($item['ieps_rate'] / 100);
                $taxAmt      = ($baseNet + $iepsAmt) * ($item['tax_rate'] / 100);

                $quotation->items()->create([
                    'product_id'      => $item['product_id'],
                    'description'     => $item['description'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_pct'    => $item['discount_pct'],
                    'discount_amount' => $discAmt,
                    'ieps_rate'       => $item['ieps_rate'],
                    'ieps_amount'     => $iepsAmt,
                    'tax_rate'        => $item['tax_rate'],
                    'subtotal'        => $baseNet + $iepsAmt + $taxAmt,
                    'unit'            => $item['unit'] ?: null,
                    'notes'           => $item['notes'] ?: null,
                ]);
            }

            // Crear solicitud de aprobación si excede límites
            if ($exceedsLimit) {
                $maxAllowed = collect($this->items)->min('max_discount_pct') ?? 0;
                $approval = DiscountApproval::create([
                    'company_id'             => $companyId,
                    'requester_id'           => auth()->id(),
                    'model_type'             => SaleQuotation::class,
                    'model_id'               => $quotation->id,
                    'status'                 => 'pending',
                    'requested_discount_pct' => (float) $this->global_discount,
                    'max_allowed_pct'        => (float) $maxAllowed,
                    'requester_notes'        => $this->approvalNotes ?: null,
                ]);
                $quotation->update(['approval_id' => $approval->id]);
            }
        });

        $this->needsApproval = false;
        if ($this->checkDiscountLimits() || true) {
            // always redirect after save
        }
        session()->flash('success', $exceedsLimit
            ? 'Cotización guardada y enviada a autorización.'
            : 'Cotización creada correctamente.');
        $this->redirect(route('sales.index'), navigate: true);
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