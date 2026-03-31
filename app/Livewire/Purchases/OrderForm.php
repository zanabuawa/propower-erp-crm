<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\SupplierBankAccount;
use App\Models\Product;
use App\Models\Branch;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class OrderForm extends Component
{
    public ?int $requisition_id = null;
    public ?int $supplier_id = null;
    public ?int $branch_id = null;
    public ?int $supplier_bank_account_id = null;
    public string $currency = 'MXN';
    public string $payment_terms = '0';
    public string $expected_at = '';
    public string $notes = '';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];
    public array $bankAccounts = [];

    public function mount(): void
    {
        $this->expected_at  = now()->addDays(7)->format('Y-m-d');
        $this->branch_id    = auth()->user()->branch_id;

        if (request()->has('requisition')) {
            $requisition = PurchaseRequisition::with('items.product')->find(request('requisition'));
            if ($requisition) {
                $this->requisition_id = $requisition->id;
                $this->currency       = $requisition->currency;
                $this->items          = $requisition->items->map(fn($i) => [
                    'product_id'  => $i->product_id,
                    'description' => $i->description,
                    'quantity'    => $i->quantity,
                    'unit_price'  => $i->unit_price,
                    'tax_rate'    => 16,
                    'unit'        => $i->unit ?? '',
                ])->toArray();
            }
        }

        if (empty($this->items)) {
            $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 16, 'unit' => '']];
        }
    }

    public function updatedSupplierId(): void
    {
        $this->bankAccounts              = [];
        $this->supplier_bank_account_id  = null;

        if ($this->supplier_id) {
            $this->bankAccounts = SupplierBankAccount::where('supplier_id', $this->supplier_id)
                ->get(['id', 'bank_name', 'account_number', 'clabe', 'is_primary'])
                ->toArray();
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
            ->get(['id', 'name', 'sku', 'purchase_price'])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $this->items[] = [
            'product_id'  => $product->id,
            'description' => $product->name,
            'quantity'    => 1,
            'unit_price'  => $product->purchase_price,
            'tax_rate'    => 16,
            'unit'        => '',
        ];

        $this->productSearch   = '';
        $this->productResults  = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 16, 'unit' => ''];
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
            'supplier_id'               => 'required|exists:suppliers,id',
            'currency'                  => 'required|in:MXN,USD',
            'payment_terms'             => 'required|integer|min:0',
            'expected_at'               => 'required|date',
            'branch_id'                 => 'nullable|exists:branches,id',
            'supplier_bank_account_id'  => 'nullable|exists:supplier_bank_accounts,id',
            'notes'                     => 'nullable|string',
            'items'                     => 'required|array|min:1',
            'items.*.description'       => 'required|string|max:255',
            'items.*.quantity'          => 'required|numeric|min:0.01',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.tax_rate'          => 'required|numeric|min:0|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $folio = 'OC-' . str_pad(
                PurchaseOrder::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $subtotal = $this->subtotal;
            $tax      = $this->tax;
            $total    = $this->total;

            $order = PurchaseOrder::create([
                'company_id'                => auth()->user()->company_id,
                'branch_id'                 => $this->branch_id,
                'supplier_id'               => $this->supplier_id,
                'purchase_requisition_id'   => $this->requisition_id,
                'created_by'                => auth()->id(),
                'folio'                     => $folio,
                'currency'                  => $this->currency,
                'status'                    => 'draft',
                'subtotal'                  => $subtotal,
                'tax'                       => $tax,
                'total'                     => $total,
                'payment_terms'             => $this->payment_terms,
                'supplier_bank_account_id'  => $this->supplier_bank_account_id,
                'notes'                     => $this->notes,
                'expected_at'               => $this->expected_at,
            ]);

            foreach ($this->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $order->items()->create([
                    'product_id'  => $item['product_id'],
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'],
                    'subtotal'    => $itemSubtotal,
                    'unit'        => $item['unit'] ?? null,
                ]);
            }
        });

        session()->flash('success', 'Orden de compra creada correctamente.');
        $this->redirect(route('purchases.orders.index'));
    }

    public function render()
    {
        return view('livewire.purchases.order-form', [
            'suppliers' => Supplier::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}