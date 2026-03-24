<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Product;
use App\Notifications\PurchaseNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class RequisitionForm extends Component
{
    public string $justification = '';
    public string $currency = 'MXN';
    public string $needed_by = '';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];

    public function mount(): void
    {
        $this->needed_by = now()->addDays(7)->format('Y-m-d');
        $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'unit' => '', 'notes' => '']];
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

    public function selectProduct(int $index, int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $this->items[$index]['product_id']   = $product->id;
        $this->items[$index]['description']  = $product->name;
        $this->items[$index]['unit_price']   = $product->purchase_price;
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'unit' => '', 'notes' => ''];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function rules(): array
    {
        return [
            'justification'          => 'required|string',
            'currency'               => 'required|in:MXN,USD',
            'needed_by'              => 'required|date',
            'items'                  => 'required|array|min:1',
            'items.*.description'    => 'required|string|max:255',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $folio = 'REQ-' . str_pad(
                PurchaseRequisition::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $requisition = PurchaseRequisition::create([
                'company_id'    => auth()->user()->company_id,
                'branch_id'     => auth()->user()->branch_id,
                'requested_by'  => auth()->id(),
                'folio'         => $folio,
                'currency'      => $this->currency,
                'status'        => 'pending_quote',
                'justification' => $this->justification,
                'needed_by'     => $this->needed_by,
            ]);

            foreach ($this->items as $item) {
                $requisition->items()->create($item);
            }

            // Notificar a usuarios de compras
            $comprasUsers = User::where('company_id', auth()->user()->company_id)
                ->whereHas('roles', fn($q) => $q->where('name', 'compras'))
                ->get();

            foreach ($comprasUsers as $user) {
                $user->notify(new PurchaseNotification(
                    title: 'Nueva requisición de compra',
                    message: "Se creó la requisición {$folio} por " . auth()->user()->name,
                    type: 'requisition',
                    requisitionId: $requisition->id,
                ));
            }
        });

        session()->flash('success', 'Requisición enviada correctamente.');
        $this->redirect(route('purchases.index'));
    }

    public function render()
    {
        return view('livewire.purchases.requisition-form');
    }
}
