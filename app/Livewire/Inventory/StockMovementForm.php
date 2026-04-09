<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\FifoStockService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
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

    #[On('product-picked')]
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
            // Solo entradas: datos del lote
            'expiry_date'  => '',
            'lot_notes'    => '',
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
            'items.*.expiry_date'        => 'nullable|date',
            'items.*.lot_notes'          => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $companyId = auth()->user()->company_id;
            $fifo      = app(FifoStockService::class);

            $folio = 'MOV-' . strtoupper($this->type[0]) . '-' . str_pad(
                StockMovement::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $movement = StockMovement::create([
                'company_id'               => $companyId,
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

                $quantityBefore = (float) $stock->quantity;
                $lotId          = null;

                if (\in_array($this->type, ['entry', 'return'])) {
                    $stock->increment('quantity', $item['quantity']);

                    $movType = $this->type === 'return' ? 'return_sale' : 'purchase';
                    $lot     = $fifo->createLot(
                        companyId:    $companyId,
                        productId:    $item['product_id'],
                        warehouseId:  $this->warehouse_id,
                        quantity:     (float) $item['quantity'],
                        unitCost:     (float) $item['unit_price'],
                        reference:    $folio,
                        expiryDate:   $item['expiry_date'] ?: null,
                        notes:        $item['lot_notes'] ?? '',
                        movementType: $movType,
                        movedAt:      now(),
                    );
                    $lotId = $lot->id;

                } elseif ($this->type === 'exit') {
                    $stock->decrement('quantity', $item['quantity']);
                    // Las salidas manuales no tienen precio de venta conocido;
                    // el kardex las registra como 'other' sin utilidad.
                    $fifo->consumeLots(
                        allocations:   $fifo->suggestAllocations(
                            $item['product_id'],
                            $this->warehouse_id,
                            (float) $item['quantity']
                        ),
                        unitSalePrice: 0,
                        movementType:  'other',
                        reference:     $folio,
                        companyId:     $companyId,
                        warehouseId:   $this->warehouse_id,
                        movedAt:       now(),
                        notes:         $this->notes,
                    );

                } elseif ($this->type === 'adjustment') {
                    $diff = (float) $item['quantity'] - $quantityBefore;
                    $stock->update(['quantity' => $item['quantity']]);

                    if ($diff > 0) {
                        $lot   = $fifo->createLot(
                            companyId:    $companyId,
                            productId:    $item['product_id'],
                            warehouseId:  $this->warehouse_id,
                            quantity:     $diff,
                            unitCost:     (float) $item['unit_price'],
                            reference:    $folio,
                            movementType: 'adjustment_in',
                            movedAt:      now(),
                        );
                        $lotId = $lot->id;
                    } elseif ($diff < 0) {
                        $fifo->consumeLots(
                            allocations:   $fifo->suggestAllocations(
                                $item['product_id'],
                                $this->warehouse_id,
                                abs($diff)
                            ),
                            unitSalePrice: 0,
                            movementType:  'adjustment_out',
                            reference:     $folio,
                            companyId:     $companyId,
                            warehouseId:   $this->warehouse_id,
                            movedAt:       now(),
                        );
                    }

                } elseif ($this->type === 'transfer') {
                    $stock->decrement('quantity', $item['quantity']);
                    $destStock = Stock::firstOrCreate(
                        ['product_id' => $item['product_id'], 'warehouse_id' => $this->warehouse_destination_id],
                        ['quantity' => 0]
                    );
                    $destStock->increment('quantity', $item['quantity']);

                    // Salida del almacén origen
                    $fifo->consumeLots(
                        allocations:   $fifo->suggestAllocations(
                            $item['product_id'],
                            $this->warehouse_id,
                            (float) $item['quantity']
                        ),
                        unitSalePrice: 0,
                        movementType:  'transfer_out',
                        reference:     $folio,
                        companyId:     $companyId,
                        warehouseId:   $this->warehouse_id,
                        movedAt:       now(),
                    );

                    // Entrada en almacén destino
                    $lot   = $fifo->createLot(
                        companyId:    $companyId,
                        productId:    $item['product_id'],
                        warehouseId:  $this->warehouse_destination_id,
                        quantity:     (float) $item['quantity'],
                        unitCost:     (float) $item['unit_price'],
                        reference:    $folio,
                        movementType: 'transfer_in',
                        movedAt:      now(),
                    );
                    $lotId = $lot->id;
                }

                $movement->items()->create([
                    'product_id'               => $item['product_id'],
                    'warehouse_id'             => $this->warehouse_id,
                    'warehouse_destination_id' => $this->warehouse_destination_id,
                    'lot_id'                   => $lotId,
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
