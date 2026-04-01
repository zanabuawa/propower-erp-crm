<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class StockMovementForm extends Component
{
    public ?StockMovement $stockMovement = null;
    public string $type = 'entry';
    public ?int $warehouse_id = null;
    public ?int $warehouse_destination_id = null;
    public string $reference = '';
    public string $notes = '';
    public string $moved_at = '';
    public array $items = [];
    public string $productSearch = '';
    public array $productResults = [];

    public function mount($stockMovement = null): void
    {
        $this->moved_at = now()->format('Y-m-d\TH:i');

        if ($stockMovement) {
            $this->stockMovement              = $stockMovement instanceof StockMovement
                ? $stockMovement
                : StockMovement::with('items.product')->findOrFail($stockMovement);
            $this->type                       = $this->stockMovement->type;
            $this->warehouse_id               = $this->stockMovement->warehouse_id;
            $this->warehouse_destination_id   = $this->stockMovement->warehouse_destination_id;
            $this->reference                  = $this->stockMovement->reference ?? '';
            $this->notes                      = $this->stockMovement->notes ?? '';
            $this->moved_at                   = $this->stockMovement->moved_at->format('Y-m-d\TH:i');
            $this->items                      = $this->stockMovement->items->map(fn($item) => [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name,
                'sku'          => $item->product->sku,
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
            ])->toArray();
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
            ->get(['id', 'name', 'sku', 'purchase_price', 'sale_price'])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $exists = collect($this->items)->firstWhere('product_id', $productId);
        if ($exists) {
            $this->productSearch = '';
            $this->productResults = [];
            return;
        }

        $this->items[] = [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'sku'          => $product->sku,
            'quantity'     => 1,
            'unit_price'   => $this->type === 'entry'
                ? $product->purchase_price
                : $product->sale_price,
        ];

        $this->productSearch = '';
        $this->productResults = [];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function updatedType(): void
    {
        $this->warehouse_destination_id = null;
    }

    public function rules(): array
    {
        return [
            'type'                       => 'required|in:entry,exit,adjustment,transfer,return',
            'warehouse_id'               => 'required|exists:warehouses,id',
            'warehouse_destination_id'   => 'nullable|exists:warehouses,id',
            'reference'                  => 'nullable|string|max:255',
            'notes'                      => 'nullable|string',
            'moved_at'                   => 'required|date',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.unit_price'         => 'required|numeric|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $folio = 'MOV-' . strtoupper($this->type[0]) . '-' . str_pad(
                StockMovement::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $movement = StockMovement::create([
                'company_id'               => auth()->user()->company_id,
                'warehouse_id'             => $this->warehouse_id,
                'warehouse_destination_id' => $this->warehouse_destination_id,
                'user_id'                  => auth()->id(),
                'type'                     => $this->type,
                'folio'                    => $folio,
                'status'                   => 'confirmed',
                'reference'                => $this->reference ?: null,
                'notes'                    => $this->notes ?: null,
                'moved_at'                 => $this->moved_at,
            ]);

            foreach ($this->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'warehouse_id' => $this->warehouse_id],
                    ['quantity' => 0]
                );

                $quantityBefore = $stock->quantity;

                if (in_array($this->type, ['entry', 'return'])) {
                    $stock->increment('quantity', $item['quantity']);
                } elseif (in_array($this->type, ['exit'])) {
                    $stock->decrement('quantity', $item['quantity']);
                } elseif ($this->type === 'adjustment') {
                    $stock->update(['quantity' => $item['quantity']]);
                } elseif ($this->type === 'transfer') {
                    $stock->decrement('quantity', $item['quantity']);
                    $destStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $this->warehouse_destination_id],
                        ['quantity' => 0]
                    );
                    $destStock->increment('quantity', $item['quantity']);
                }

                $movement->items()->create([
                    'product_id'               => $item['product_id'],
                    'warehouse_id'             => $this->warehouse_id,
                    'warehouse_destination_id' => $this->warehouse_destination_id,
                    'quantity'                 => $item['quantity'],
                    'unit_price'               => $item['unit_price'],
                    'quantity_before'          => $quantityBefore,
                    'quantity_after'           => $stock->fresh()->quantity,
                ]);
            }
        });

        session()->flash('success', 'Movimiento registrado correctamente.');
        $this->redirect(route('inventory.movements.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.stock-movement-form', [
            'warehouses' => Warehouse::where('is_active', true)->with('branch')->orderBy('name')->get(),
        ]);
    }
}
