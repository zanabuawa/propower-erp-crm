<?php

namespace App\Livewire\Inventory;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\ApprovalOtpCode;
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
    public string  $type                     = 'entry';
    public ?int    $warehouse_id             = null;
    public ?int    $warehouse_destination_id = null;
    public string  $reference               = '';
    public string  $notes                   = '';
    public string  $moved_at                = '';
    public array   $items                   = [];
    public string  $productSearch           = '';
    public array   $productResults          = [];

    // Ajuste
    public string  $adjustment_reason       = '';

    // Aprobación por OTP
    public bool    $requiresApproval        = false;
    public bool    $showOtpModal            = false;
    public string  $otpCode                 = '';
    public string  $otpError                = '';
    public ?int    $pendingMovementId       = null;

    // Afectación contable
    public ?int    $finance_account_id      = null;

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

    #[On('products-picked')]
    public function productsPicked(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->addProduct((int) $productId);
        }
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
        $this->adjustment_reason        = '';
        $this->requiresApproval         = false;
    }

    public function updatedAdjustmentReason(): void
    {
        $this->requiresApproval = $this->type === 'adjustment'
            && in_array($this->adjustment_reason, StockMovement::REQUIRES_APPROVAL_REASONS, true);
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
            'adjustment_reason'          => 'required_if:type,adjustment',
            'finance_account_id'         => 'nullable|exists:finance_accounts,id',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'items.*.expiry_date'        => 'nullable|date',
            'items.*.lot_notes'          => 'nullable|string|max:255',
        ];
    }

    public function sendOtp(): void
    {
        $plainCode = ApprovalOtpCode::generate(auth()->user(), 'stock_adjustment', $this->pendingMovementId);
        // Enviar por notificación/correo
        auth()->user()->notify(new \App\Notifications\ApprovalOtpNotification(
            title:   'Aprobación de ajuste de inventario',
            message: "Código OTP para aprobar el ajuste de inventario {$this->stockMovement?->folio}: {$plainCode}",
        ));
        session()->flash('otp_sent', 'Código enviado a tu correo.');
    }

    public function verifyOtp(): void
    {
        $movement = StockMovement::find($this->pendingMovementId);

        if (! $movement) {
            $this->otpError = 'Movimiento no encontrado.';
            return;
        }

        $valid = ApprovalOtpCode::verify(auth()->user(), 'stock_adjustment', $this->pendingMovementId, $this->otpCode);

        if (! $valid) {
            $this->otpError = 'Código incorrecto o expirado.';
            return;
        }

        $movement->update([
            'status'      => 'confirmed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Procesar el ajuste y afectación contable
        $this->processConfirmedAdjustment($movement);

        $this->showOtpModal      = false;
        $this->pendingMovementId = null;
        $this->otpCode           = '';

        session()->flash('success', 'Ajuste aprobado y confirmado.');
        $this->redirect(route('inventory.movements.index'), navigate: true);
    }

    private function processConfirmedAdjustment(StockMovement $movement): void
    {
        $fifo      = app(FifoStockService::class);
        $companyId = $movement->company_id;

        foreach ($movement->items as $item) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $item->product_id, 'warehouse_id' => $movement->warehouse_id],
                ['quantity' => 0, 'committed_quantity' => 0]
            );

            $quantityBefore = (float) $stock->quantity;
            $diff = (float) $item->quantity - $quantityBefore;
            $stock->update(['quantity' => $item->quantity]);

            if ($diff > 0) {
                $fifo->createLot(
                    companyId: $companyId, productId: $item->product_id,
                    warehouseId: $movement->warehouse_id, quantity: $diff,
                    unitCost: (float) $item->unit_price, reference: $movement->folio,
                    movementType: 'adjustment_in', movedAt: now(),
                );
            } elseif ($diff < 0) {
                $fifo->consumeLots(
                    allocations: $fifo->suggestAllocations($item->product_id, $movement->warehouse_id, abs($diff)),
                    unitSalePrice: 0, movementType: 'adjustment_out',
                    reference: $movement->folio, companyId: $companyId,
                    warehouseId: $movement->warehouse_id, movedAt: now(),
                );
            }

            $item->update(['quantity_before' => $quantityBefore, 'quantity_after' => $item->quantity]);
        }

        // Afectación contable automática si hay cuenta configurada
        if ($movement->finance_account_id) {
            $totalValue = $movement->items->sum(fn($i) => abs((float)$i->quantity - (float)$i->quantity_before) * (float)$i->unit_price);
            if ($totalValue > 0) {
                $folio = 'ASIENTO-' . str_pad(FinanceTransaction::count() + 1, 6, '0', STR_PAD_LEFT);
                $transaction = FinanceTransaction::create([
                    'account_id'       => $movement->finance_account_id,
                    'registered_by'    => $movement->user_id,
                    'folio'            => $folio,
                    'type'             => 'expense',
                    'concept'          => 'Ajuste de inventario: ' . (StockMovement::ADJUSTMENT_REASONS[$movement->adjustment_reason] ?? $movement->adjustment_reason),
                    'category'         => 'inventario',
                    'amount'           => $totalValue,
                    'currency'         => 'MXN',
                    'exchange_rate'    => 1,
                    'transaction_date' => now()->toDateString(),
                    'reference'        => $movement->folio,
                    'status'           => 'confirmed',
                    'notes'            => $movement->notes,
                ]);
                $movement->update(['finance_transaction_id' => $transaction->id]);
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $companyId  = auth()->user()->company_id;
            $fifo       = app(FifoStockService::class);
            $needsOtp   = $this->type === 'adjustment' && $this->requiresApproval;

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
                'status'                   => $needsOtp ? 'pending_approval' : 'confirmed',
                'reference'                => $this->reference ?: null,
                'notes'                    => $this->notes ?: null,
                'moved_at'                 => $this->moved_at,
                'adjustment_reason'        => $this->type === 'adjustment' ? ($this->adjustment_reason ?: null) : null,
                'finance_account_id'       => $this->finance_account_id ?: null,
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
            // Si el ajuste NO requiere aprobación, procesar afectación contable ahora
            if ($this->type === 'adjustment' && ! $needsOtp && $this->finance_account_id) {
                $this->processConfirmedAdjustment($movement);
            }

            if ($needsOtp) {
                $this->pendingMovementId = $movement->id;
            }
        });

        if ($this->requiresApproval && $this->pendingMovementId) {
            $this->showOtpModal = true;
            $this->sendOtp();
            return;
        }

        session()->flash('success', 'Movimiento registrado correctamente.');
        $this->redirect(route('inventory.movements.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.stock-movement-form', [
            'warehouses'      => Warehouse::where('is_active', true)->with('branch')->orderBy('name')->get(),
            'financeAccounts' => FinanceAccount::where('company_id', auth()->user()->company_id)
                ->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }
}
