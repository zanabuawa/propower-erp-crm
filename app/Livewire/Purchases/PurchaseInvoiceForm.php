<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PurchaseInvoiceForm extends Component
{
    // Header
    public ?int    $orderId                = null;
    public ?int    $supplierId             = null;
    public string  $supplierInvoiceNumber  = '';
    public string  $currency              = 'MXN';
    public string  $issuedAt              = '';
    public string  $receivedAt            = '';
    public string  $dueAt                 = '';
    public string  $notes                 = '';
    public int     $paymentTermsDays      = 30;

    // Items
    public array $items = [];

    // Options
    public array $orderOptions    = [];
    public array $supplierOptions = [];

    // Estado de carga de OC
    public ?array $loadedOrder = null;

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->issuedAt   = now()->toDateString();
        $this->receivedAt = now()->toDateString();

        $this->supplierOptions = Supplier::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')
            ->get(['id', 'name', 'payment_terms'])->toArray();

        $this->orderOptions = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', ['received', 'partial_received', 'waiting_delivery', 'invoiced'])
            ->with('supplier:id,name')
            ->latest()->get(['id', 'folio', 'supplier_id', 'currency', 'total', 'payment_terms'])
            ->toArray();

        // Pre-selección via query string ?order=
        $orderId = request()->query('order');
        if ($orderId) {
            $this->orderId = (int) $orderId;
            $this->loadOrderItems();
        } else {
            $this->addItem();
        }
    }

    public function updatedOrderId(): void
    {
        $this->loadOrderItems();
    }

    public function updatedSupplierId(): void
    {
        $sup = collect($this->supplierOptions)->firstWhere('id', (int) $this->supplierId);
        if ($sup && $sup['payment_terms']) {
            $this->paymentTermsDays = (int) $sup['payment_terms'];
            $this->recalcDueDate();
        }
    }

    public function updatedIssuedAt(): void
    {
        $this->recalcDueDate();
    }

    private function recalcDueDate(): void
    {
        if ($this->issuedAt && $this->paymentTermsDays > 0) {
            $this->dueAt = \Carbon\Carbon::parse($this->issuedAt)
                ->addDays($this->paymentTermsDays)->toDateString();
        }
    }

    // ── Cargar ítems desde la OC ─────────────────────────────────────────
    public function loadOrderItems(): void
    {
        if (! $this->orderId) {
            $this->loadedOrder = null;
            $this->items       = [];
            $this->addItem();
            return;
        }

        $order = PurchaseOrder::with([
            'supplier',
            'items.product',
            'receipts.items',
        ])->find((int) $this->orderId);

        if (! $order) return;

        $this->supplierId        = $order->supplier_id;
        $this->currency          = $order->currency;
        $this->paymentTermsDays  = (int) ($order->payment_terms ?? 30);
        $this->recalcDueDate();

        $this->loadedOrder = [
            'id'    => $order->id,
            'folio' => $order->folio,
            'total' => $order->total,
        ];

        // Calcular cantidad recibida por ítem de OC
        $receivedByItem = [];
        foreach ($order->receipts as $receipt) {
            foreach ($receipt->items as $ri) {
                $key = $ri->purchase_order_item_id;
                $receivedByItem[$key] = ($receivedByItem[$key] ?? 0) + (float) $ri->quantity_received;
            }
        }

        $this->items = [];
        foreach ($order->items as $oi) {
            $qtyReceived = $receivedByItem[$oi->id] ?? 0;
            $this->items[] = [
                'purchase_order_item_id' => $oi->id,
                'product_id'             => $oi->product_id,
                'description'            => $oi->description,
                'quantity'               => (string) $qtyReceived ?: (string) (float) $oi->quantity,
                'unit_price'             => (string) (float) $oi->unit_price,
                'tax_rate'               => (string) (float) $oi->tax_rate,
                'subtotal'               => round($qtyReceived * (float) $oi->unit_price, 2),
                // Referencia OC
                'qty_ordered'            => (float) $oi->quantity,
                'qty_received'           => (float) $qtyReceived,
                'price_ordered'          => (float) $oi->unit_price,
            ];
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    // ── Items manuales ───────────────────────────────────────────────────
    public function addItem(): void
    {
        $this->items[] = [
            'purchase_order_item_id' => null,
            'product_id'             => null,
            'description'            => '',
            'quantity'               => '1',
            'unit_price'             => '0',
            'tax_rate'               => '16',
            'subtotal'               => 0,
            'qty_ordered'            => null,
            'qty_received'           => null,
            'price_ordered'          => null,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) return;
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key): void
    {
        [$index] = explode('.', $key);
        $this->recalcItem((int) $index);
    }

    private function recalcItem(int $i): void
    {
        $qty  = max(0, (float) ($this->items[$i]['quantity']   ?? 0));
        $prc  = max(0, (float) ($this->items[$i]['unit_price'] ?? 0));
        $this->items[$i]['subtotal'] = round($qty * $prc, 2);
    }

    // ── Totales computed ─────────────────────────────────────────────────
    public function getSubtotalProperty(): float
    {
        return round(collect($this->items)->sum(fn($i) => (float) ($i['subtotal'] ?? 0)), 2);
    }

    public function getTaxAmountProperty(): float
    {
        return round(collect($this->items)->sum(function ($i) {
            return (float) ($i['subtotal'] ?? 0) * (float) ($i['tax_rate'] ?? 0) / 100;
        }), 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 2);
    }

    // ── Guardar ──────────────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'supplierId'           => 'required|exists:suppliers,id',
            'supplierInvoiceNumber'=> 'required|string|max:100',
            'issuedAt'             => 'required|date',
            'dueAt'                => 'required|date|after_or_equal:issuedAt',
            'items'                => 'required|array|min:1',
            'items.*.description'  => 'required|string|max:255',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.tax_rate'     => 'required|numeric|min:0|max:100',
        ], [
            'supplierId.required'            => 'Selecciona el proveedor.',
            'supplierInvoiceNumber.required' => 'El número de factura del proveedor es requerido.',
            'issuedAt.required'              => 'La fecha de emisión es requerida.',
            'dueAt.required'                 => 'La fecha de vencimiento es requerida.',
            'dueAt.after_or_equal'           => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'items.*.description.required'   => 'Cada partida requiere una descripción.',
            'items.*.quantity.min'           => 'La cantidad debe ser mayor a cero.',
        ]);

        try {
            DB::transaction(function () {
                $companyId = auth()->user()->company_id;

                $folio = 'FP-' . str_pad(
                    PurchaseInvoice::where('company_id', $companyId)->count() + 1,
                    6, '0', STR_PAD_LEFT
                );

                $invoice = PurchaseInvoice::create([
                    'company_id'             => $companyId,
                    'purchase_order_id'      => $this->orderId ?: null,
                    'supplier_id'            => $this->supplierId,
                    'created_by'             => auth()->id(),
                    'folio'                  => $folio,
                    'supplier_invoice_number'=> $this->supplierInvoiceNumber,
                    'currency'               => $this->currency,
                    'subtotal'               => $this->subtotal,
                    'tax'                    => $this->taxAmount,
                    'total'                  => $this->total,
                    'paid_amount'            => 0,
                    'status'                 => 'pending',
                    'match_status'           => 'pending',
                    'notes'                  => $this->notes ?: null,
                    'issued_at'              => $this->issuedAt,
                    'received_at'            => $this->receivedAt ?: null,
                    'due_at'                 => $this->dueAt,
                ]);

                foreach ($this->items as $item) {
                    PurchaseInvoiceItem::create([
                        'purchase_invoice_id'      => $invoice->id,
                        'purchase_order_item_id'   => $item['purchase_order_item_id'] ?: null,
                        'product_id'               => $item['product_id'] ?: null,
                        'description'              => $item['description'],
                        'quantity'                 => $item['quantity'],
                        'unit_price'               => $item['unit_price'],
                        'tax_rate'                 => $item['tax_rate'],
                        'subtotal'                 => $item['subtotal'],
                        'qty_ordered'              => $item['qty_ordered'] ?? null,
                        'qty_received'             => $item['qty_received'] ?? null,
                        'price_ordered'            => $item['price_ordered'] ?? null,
                        'match_status'             => 'unmatched',
                    ]);
                }

                // Actualizar OC a estado 'invoiced' si aplica
                if ($this->orderId) {
                    PurchaseOrder::where('id', $this->orderId)
                        ->whereNotIn('status', ['cancelled'])
                        ->update(['status' => 'invoiced']);
                }

                // Correr 3-way match automático
                $invoice->load('items');
                $invoice->runThreeWayMatch();

                session()->flash('success', "Factura {$folio} registrada. Match: " .
                    PurchaseInvoice::MATCH_STATUS[$invoice->fresh()->match_status] . '.');

                $this->redirectRoute('purchases.invoices.show', $invoice, navigate: true);
            });

        } catch (\Throwable $e) {
            Log::error('PurchaseInvoiceForm save', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchases.purchase-invoice-form', [
            'subtotal'  => $this->subtotal,
            'taxAmount' => $this->taxAmount,
            'total'     => $this->total,
        ]);
    }
}
