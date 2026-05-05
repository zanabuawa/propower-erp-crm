<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\DiscountApproval;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\SaleQuotation;
use App\Models\User;
use App\Notifications\DiscountApprovalNotification;
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
        return [
            'product_id'         => null,
            'description'        => '',
            'quantity'           => 1,
            'unit_price'         => 0,
            'discount_pct'       => 0,
            'tax_rate'           => 16,
            'ieps_rate'          => 0,
            'unit'               => '',
            'notes'              => '',
            'min_sale_price'     => 0,
            'max_discount_pct'   => 100,
            'global_discount_cap'=> 100,
        ];
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

        $cost             = (float) $product->purchase_price;
        $opDiv            = 1 - (float)$product->operational_costs / 100;
        $minSalePrice     = $opDiv > 0 ? round($cost / $opDiv, 2) : 0;
        $maxDiscountPct   = $price > 0
            ? round(max(0, ($price - $minSalePrice) / $price * 100), 4)
            : 0;
        // Cap global = (margen_utilidad + gastos_op) / 2
        $globalDiscountCap = round(((float)$product->profit_margin + (float)$product->operational_costs) / 2, 4);

        $this->items[] = [
            'product_id'          => $product->id,
            'description'         => $product->name,
            'quantity'            => 1,
            'unit_price'          => $price,
            'discount_pct'        => 0,
            'tax_rate'            => 16,
            'ieps_rate'           => (float) $product->ieps_rate,
            'unit'                => $product->unitOfMeasure?->abbreviation ?? '',
            'notes'               => '',
            'min_sale_price'      => $minSalePrice,
            'max_discount_pct'    => $maxDiscountPct,
            'global_discount_cap' => $globalDiscountCap,
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

    /** Cap del descuento global sin autorización = mínimo de (margen+gastos_op)/2 entre todos los ítems. */
    public function getMaxGlobalDiscountCapProperty(): float
    {
        if (empty($this->items)) return 100;
        $caps = collect($this->items)->pluck('global_discount_cap')->filter(fn($v) => $v < 100);
        return $caps->isEmpty() ? 100 : (float) $caps->min();
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

    /**
     * Verifica límites de descuento.
     * Retorna ['exceeds'=>bool, 'requested'=>float, 'max_allowed'=>float]
     */
    private function checkDiscountLimits(): array
    {
        $globalDisc = (float) $this->global_discount;
        $globalCap  = $this->maxGlobalDiscountCap;

        // 1. Descuento global supera el cap permitido sin autorización
        if ($globalDisc > $globalCap) {
            $this->exceedingMaxPct = $globalCap;
            return ['exceeds' => true, 'requested' => $globalDisc, 'max_allowed' => $globalCap];
        }

        foreach ($this->items as $item) {
            $minPrice  = (float) ($item['min_sale_price'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discPct   = (float) ($item['discount_pct'] ?? 0);
            $maxPct    = (float) ($item['max_discount_pct'] ?? 100);

            // 2. Descuento por línea excede su máximo individual
            if ($discPct > $maxPct) {
                $this->exceedingMaxPct = $maxPct;
                return ['exceeds' => true, 'requested' => $discPct, 'max_allowed' => $maxPct];
            }

            // 3. Precio efectivo combinado (línea + global) cae por debajo del precio mínimo
            if ($unitPrice > 0 && $minPrice > 0) {
                $effectivePrice = $unitPrice * (1 - $discPct / 100) * (1 - $globalDisc / 100);
                if ($effectivePrice < $minPrice) {
                    $effectivePct = round((1 - (1 - $discPct / 100) * (1 - $globalDisc / 100)) * 100, 2);
                    $this->exceedingMaxPct = $maxPct;
                    return ['exceeds' => true, 'requested' => $effectivePct, 'max_allowed' => $maxPct];
                }
            }
        }

        return ['exceeds' => false, 'requested' => 0.0, 'max_allowed' => 0.0];
    }

    public function save(bool $forceApproval = false): void
    {
        $this->validate();

        $limitCheck   = $this->checkDiscountLimits();
        $exceedsLimit = $limitCheck['exceeds'];

        if ($exceedsLimit && !$forceApproval) {
            $this->needsApproval = true;
            return;
        }

        DB::transaction(function () use ($exceedsLimit, $limitCheck) {
            $companyId = auth()->user()->company_id;
            $folio = 'COT-' . str_pad(
                SaleQuotation::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $quotation = SaleQuotation::create([
                'company_id'      => $companyId,
                'branch_id'       => auth()->user()->branch_id,
                'customer_id'     => $this->customer_id,
                'price_list_id'   => $this->price_list_id,
                'created_by'      => auth()->id(),
                'folio'           => $folio,
                'currency'        => $this->currency,
                'status'          => 'draft',
                'approval_status' => $exceedsLimit ? 'pending' : null,
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
                $base    = $item['quantity'] * $item['unit_price'];
                $discAmt = $base * ($item['discount_pct'] / 100);
                $baseNet = $base - $discAmt;
                $iepsAmt = $baseNet * ($item['ieps_rate'] / 100);
                $taxAmt  = ($baseNet + $iepsAmt) * ($item['tax_rate'] / 100);

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

            if ($exceedsLimit) {
                $approval = DiscountApproval::create([
                    'company_id'             => $companyId,
                    'requester_id'           => auth()->id(),
                    'model_type'             => SaleQuotation::class,
                    'model_id'               => $quotation->id,
                    'status'                 => 'pending',
                    'requested_discount_pct' => $limitCheck['requested'],
                    'max_allowed_pct'        => $limitCheck['max_allowed'],
                    'requester_notes'        => $this->approvalNotes ?: null,
                ]);
                $quotation->update(['approval_id' => $approval->id]);

                // Notificar a todos los usuarios con permiso de aprobar descuentos
                User::where('company_id', $companyId)
                    ->permission('approve discounts')
                    ->get()
                    ->each(fn($u) => $u->notify(new DiscountApprovalNotification(
                        type:          'requested',
                        folio:         $quotation->folio,
                        requestedPct:  $limitCheck['requested'],
                        maxAllowedPct: $limitCheck['max_allowed'],
                        notes:         $this->approvalNotes ?: null,
                        approvalId:    $approval->id,
                    )));
            }
        });

        $this->needsApproval = false;
        session()->flash('success', $exceedsLimit
            ? 'Cotización guardada y enviada a autorización de descuento.'
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