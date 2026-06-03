<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use App\Models\PurchaseQuotation;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Branch;
use App\Notifications\PurchaseNotification;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class OrderForm extends Component
{
    public ?int    $requisition_id = null;
    public ?int    $branch_id      = null;
    public string  $currency       = 'MXN';
    public string  $payment_terms  = '0';
    public string  $expected_at    = '';
    public string  $required_at    = '';
    public string  $shipping_address = '';
    public string  $billing_address  = '';
    public string  $print_language   = 'es';
    public string  $notes            = '';
    public array   $items            = [];
    public string  $productSearch    = '';
    public array   $productResults   = [];
    public ?string $sourceLabel      = null;

    public function mount(): void
    {
        $this->expected_at = now()->addDays(7)->format('Y-m-d');
        $this->branch_id   = auth()->user()->branch_id;

        // Pre-llenar desde cotización final autorizada
        if (request()->has('quotation')) {
            $quotation = PurchaseQuotation::with(['items', 'requisition'])->find(request('quotation'));
            if ($quotation && $quotation->requisition) {
                $this->requisition_id = $quotation->requisition->id;
                $this->currency       = $quotation->requisition->currency;
                $this->sourceLabel    = $quotation->requisition->folio;
                $this->items          = $quotation->items->map(fn($i) => [
                    'product_id'  => $i->product_id,
                    'supplier_id' => null,
                    'description' => $i->description,
                    'quantity'    => (float) $i->quantity,
                    'unit_price'  => (float) $i->unit_price,
                    'tax_rate'    => (float) $i->tax_rate,
                    'unit'        => $i->unit ?? '',
                ])->toArray();
            }
        }

        // Pre-llenar desde requisición directa
        if (empty($this->items) && request()->has('requisition')) {
            $requisition = PurchaseRequisition::with('items.product')->find(request('requisition'));
            if ($requisition) {
                $this->requisition_id = $requisition->id;
                $this->currency       = $requisition->currency;
                $this->sourceLabel    = $requisition->folio;
                $this->items          = $requisition->items->map(fn($i) => [
                    'product_id'  => $i->product_id,
                    'supplier_id' => null,
                    'description' => $i->description,
                    'quantity'    => $i->quantity,
                    'unit_price'  => $i->unit_price,
                    'tax_rate'    => 16,
                    'unit'        => $i->unit ?? '',
                ])->toArray();
            }
        }

        if (empty($this->items)) {
            $this->items = [['product_id' => null, 'supplier_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 16, 'unit' => '']];
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
                ->orWhere('sku',  'like', "%{$this->productSearch}%")
                ->orWhere('barcode', 'like', "%{$this->productSearch}%"))
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'purchase_price'])
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

        // Si el precio de obtención ya incluye IVA, no se agrega IVA adicional en compras
        $taxRate = $product->purchase_price_includes_iva ? 0 : 16;

        $newItem = [
            'product_id'  => $product->id,
            'supplier_id' => $product->supplier_id ?? null,
            'description' => $product->name,
            'quantity'    => 1,
            'unit_price'  => $product->purchase_price,
            'tax_rate'    => $taxRate,
            'unit'        => '',
        ];

        // Buscar si hay una partida vacía para reemplazarla
        $emptyIndex = collect($this->items)->search(function($item) {
            return empty($item['product_id']) && (empty($item['description']) || $item['description'] === '');
        });

        if ($emptyIndex !== false) {
            $this->items[$emptyIndex] = $newItem;
        } else {
            $this->items[] = $newItem;
        }

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'supplier_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 16, 'unit' => ''];
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

    public function getTaxProperty(): float
    {
        return collect($this->items)->sum(fn($i) =>
            ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (($i['tax_rate'] ?? 0) / 100)
        );
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax;
    }

    public function rules(): array
    {
        return [
            'currency'            => 'required|in:MXN,USD',
            'payment_terms'       => 'required|integer|min:0',
            'expected_at'         => 'required|date',
            'required_at'         => 'nullable|date',
            'shipping_address'    => 'nullable|string|max:500',
            'billing_address'     => 'nullable|string|max:500',
            'print_language'      => 'required|in:es,en',
            'branch_id'           => 'nullable|exists:branches,id',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.supplier_id' => 'nullable|exists:suppliers,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Evitar duplicar OC para la misma requisición
        if ($this->requisition_id) {
            $existing = PurchaseOrder::where('purchase_requisition_id', $this->requisition_id)
                ->whereNotIn('status', ['cancelled'])
                ->first();
            if ($existing) {
                $this->addError('requisition_id',
                    "Ya existe una orden de compra activa ({$existing->folio}) para esta requisición.");
                return;
            }
        }

        DB::transaction(function () {
            $folio = 'OC-' . str_pad(
                PurchaseOrder::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $order = PurchaseOrder::create([
                'company_id'              => auth()->user()->company_id,
                'branch_id'               => $this->branch_id,
                'purchase_requisition_id' => $this->requisition_id,
                'created_by'              => auth()->id(),
                'folio'                   => $folio,
                'currency'                => $this->currency,
                'status'                  => 'draft',
                'subtotal'                => $this->subtotal,
                'tax'                     => $this->tax,
                'total'                   => $this->total,
                'payment_terms'           => $this->payment_terms,
                'notes'                   => $this->notes,
                'expected_at'             => $this->expected_at,
                'required_at'             => $this->required_at ?: null,
                'shipping_address'        => $this->shipping_address ?: null,
                'billing_address'         => $this->billing_address ?: null,
                'print_language'          => $this->print_language,
            ]);

            foreach ($this->items as $item) {
                $order->items()->create([
                    'supplier_id' => $item['supplier_id'] ?? null,
                    'product_id'  => $item['product_id'],
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'],
                    'subtotal'    => $item['quantity'] * $item['unit_price'],
                    'unit'        => $item['unit'] ?? null,
                ]);
            }

            // Notificar al requisitor si viene de una requisición
            if ($this->requisition_id) {
                $requisition = PurchaseRequisition::with('requestedBy')->find($this->requisition_id);
                if ($requisition && $requisition->requestedBy) {
                    $requisition->update(['status' => 'ordered']);
                    $requisition->requestedBy->notify(new PurchaseNotification(
                        title: 'Orden de compra generada',
                        message: "Se generó la orden de compra {$folio} para tu requisición {$requisition->folio}. El proveedor será contactado a la brevedad.",
                        type: 'order_created',
                        requisitionId: $requisition->id,
                        orderId: $order->id,
                    ));
                }
            }
        });

        session()->flash('success', 'Orden de compra creada correctamente.');
        $this->redirect(route('purchases.orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.order-form', [
            'suppliers' => Supplier::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
