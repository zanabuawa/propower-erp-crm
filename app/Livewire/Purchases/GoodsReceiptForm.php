<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class GoodsReceiptForm extends Component
{
    public ?int    $supplier_id  = null;
    public ?int    $warehouse_id = null;
    public string  $notes        = '';
    public string  $reference    = ''; // factura, remisión, etc.
    public array   $items        = [];

    // Búsqueda de productos
    public string  $productSearch   = '';
    public array   $productResults  = [];

    public function mount(): void
    {
        $this->items = [];

        // Almacén por defecto según sucursal del usuario
        $this->warehouse_id = auth()->user()->branch_id
            ? Warehouse::where('branch_id', auth()->user()->branch_id)
                ->where('is_active', true)->first()?->id
            : null;
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
            ->get(['id', 'name', 'sku', 'purchase_price', 'profit_margin', 'operational_costs'])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        // Evitar duplicados
        foreach ($this->items as $item) {
            if ($item['product_id'] === $productId) {
                $this->productSearch  = '';
                $this->productResults = [];
                return;
            }
        }

        $product = Product::find($productId);
        if (!$product) return;

        $this->items[] = [
            'product_id'       => $product->id,
            'product_name'     => $product->name,
            'sku'              => $product->sku ?? '',
            'quantity'         => 1,
            'purchase_price'   => (float) $product->purchase_price,
            'profit_margin'    => (float) $product->profit_margin,
            'operational_cost' => (float) $product->operational_costs,
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    /**
     * Precio de venta calculado para un ítem: costo * (1 + margen% / 100)
     */
    public function getSalePriceForItem(array $item): float
    {
        $cost   = (float) ($item['purchase_price'] ?? 0);
        $margin = (float) ($item['profit_margin'] ?? 0);
        return round($cost * (1 + $margin / 100), 2);
    }

    /**
     * Precio mínimo venta: costo * (1 + gastos_op% / 100)
     */
    public function getMinSalePriceForItem(array $item): float
    {
        $cost  = (float) ($item['purchase_price'] ?? 0);
        $opPct = (float) ($item['operational_cost'] ?? 0);
        return round($cost * (1 + $opPct / 100), 2);
    }

    public function rules(): array
    {
        return [
            'warehouse_id'               => 'required|exists:warehouses,id',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.purchase_price'     => 'required|numeric|min:0',
            'items.*.profit_margin'      => 'required|numeric|min:0|max:999',
            'items.*.operational_cost'   => 'required|numeric|min:0|max:999',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $companyId = auth()->user()->company_id;

            $folio = 'REC-' . str_pad(
                \App\Models\PurchaseReceipt::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            // Crear cabecera del movimiento de stock (tipo entry)
            $movement = StockMovement::create([
                'company_id'  => $companyId,
                'warehouse_id' => $this->warehouse_id,
                'user_id'     => auth()->id(),
                'type'        => 'entry',
                'folio'       => $folio,
                'status'      => 'confirmed',
                'reference'   => $this->reference ?: null,
                'notes'       => $this->notes ?: 'Recepción de mercancías',
                'moved_at'    => now(),
            ]);

            foreach ($this->items as $item) {
                $productId        = $item['product_id'];
                $qty              = (float) $item['quantity'];
                $newPurchasePrice = (float) $item['purchase_price'];
                $profitMargin     = (float) $item['profit_margin'];
                $opCostPct        = (float) $item['operational_cost'];

                // Actualizar stock
                $stock = Stock::firstOrCreate(
                    ['product_id' => $productId, 'warehouse_id' => $this->warehouse_id],
                    ['quantity' => 0]
                );
                $qtyBefore = (float) $stock->quantity;
                $stock->increment('quantity', $qty);

                // Actualizar precios del producto
                $newSalePrice = round($newPurchasePrice * (1 + $profitMargin / 100), 2);

                $productUpdate = [
                    'purchase_price'    => $newPurchasePrice,
                    'operational_costs' => $opCostPct,
                    'sale_price'        => $newSalePrice,
                ];
                if ($this->supplier_id) {
                    $productUpdate['supplier_id'] = $this->supplier_id;
                }
                Product::where('id', $productId)->update($productUpdate);

                // Ítem del movimiento de stock
                $movement->items()->create([
                    'product_id'      => $productId,
                    'warehouse_id'    => $this->warehouse_id,
                    'quantity'        => $qty,
                    'unit_price'      => $newPurchasePrice,
                    'quantity_before' => $qtyBefore,
                    'quantity_after'  => $qtyBefore + $qty,
                ]);
            }
        });

        session()->flash('success', 'Recepción de mercancías registrada correctamente.');
        $this->redirect(route('purchases.goods-receipts.index'));
    }

    public function render()
    {
        return view('livewire.purchases.goods-receipt-form', [
            'warehouses' => Warehouse::where('is_active', true)->with('branch')->orderBy('name')->get(),
            'suppliers'  => Supplier::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
