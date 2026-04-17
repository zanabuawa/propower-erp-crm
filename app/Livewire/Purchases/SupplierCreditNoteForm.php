<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierCreditNote;
use App\Models\SupplierCreditNoteItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierCreditNoteForm extends Component
{
    public ?int    $invoiceId              = null;
    public ?int    $supplierId             = null;
    public string  $supplierCreditNoteNumber = '';
    public string  $currency              = 'MXN';
    public string  $issuedAt              = '';
    public string  $reason                = 'other';
    public string  $notes                 = '';

    public array   $items                 = [];
    public array   $invoiceOptions        = [];
    public array   $supplierOptions       = [];
    public ?array  $loadedInvoice         = null;

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->issuedAt = now()->toDateString();

        $this->supplierOptions = Supplier::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')
            ->get(['id', 'name'])->toArray();

        $this->invoiceOptions = PurchaseInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['cancelled'])
            ->with('supplier:id,name')
            ->latest()->get(['id', 'folio', 'supplier_id', 'currency', 'total', 'paid_amount'])
            ->toArray();

        // Pre-selección ?invoice=
        $invoiceId = request()->query('invoice');
        if ($invoiceId) {
            $this->invoiceId = (int) $invoiceId;
            $this->loadInvoiceData();
        } else {
            $this->addItem();
        }
    }

    public function updatedInvoiceId(): void
    {
        $this->loadInvoiceData();
    }

    public function updatedSupplierId(): void
    {
        // Filtrar facturas del proveedor seleccionado
        if ($this->supplierId && !$this->invoiceId) {
            $this->invoiceOptions = PurchaseInvoice::where('company_id', auth()->user()->company_id)
                ->where('supplier_id', $this->supplierId)
                ->whereNotIn('status', ['cancelled'])
                ->with('supplier:id,name')
                ->latest()->get(['id', 'folio', 'supplier_id', 'currency', 'total', 'paid_amount'])
                ->toArray();
        }
    }

    private function loadInvoiceData(): void
    {
        if (! $this->invoiceId) {
            $this->loadedInvoice = null;
            $this->items = [];
            $this->addItem();
            return;
        }

        $inv = PurchaseInvoice::with(['supplier', 'items'])->find((int) $this->invoiceId);
        if (! $inv) return;

        $this->supplierId  = $inv->supplier_id;
        $this->currency    = $inv->currency;

        $this->loadedInvoice = [
            'id'      => $inv->id,
            'folio'   => $inv->folio,
            'total'   => $inv->total,
            'balance' => $inv->balance,
        ];

        // Pre-llenar partidas con las de la factura
        $this->items = $inv->items->map(fn($i) => [
            'product_id'  => $i->product_id,
            'description' => $i->description,
            'quantity'    => '0',
            'unit_price'  => (string)(float) $i->unit_price,
            'tax_rate'    => (string)(float) $i->tax_rate,
            'subtotal'    => 0,
        ])->toArray();

        if (empty($this->items)) $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id'  => null,
            'description' => '',
            'quantity'    => '1',
            'unit_price'  => '0',
            'tax_rate'    => '16',
            'subtotal'    => 0,
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
        $qty = max(0, (float) ($this->items[$i]['quantity']   ?? 0));
        $prc = max(0, (float) ($this->items[$i]['unit_price'] ?? 0));
        $this->items[$i]['subtotal'] = round($qty * $prc, 2);
    }

    public function getSubtotalProperty(): float
    {
        return round(collect($this->items)->sum(fn($i) => (float)($i['subtotal'] ?? 0)), 2);
    }

    public function getTaxAmountProperty(): float
    {
        return round(collect($this->items)->sum(function ($i) {
            return (float)($i['subtotal'] ?? 0) * (float)($i['tax_rate'] ?? 0) / 100;
        }), 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->taxAmount, 2);
    }

    public function save(): void
    {
        $this->validate([
            'supplierId'             => 'required|exists:suppliers,id',
            'issuedAt'               => 'required|date',
            'reason'                 => 'required|in:return,price_adjustment,duplicate,error,other',
            'items'                  => 'required|array|min:1',
            'items.*.description'    => 'required|string|max:255',
            'items.*.quantity'       => 'required|numeric|min:0.001',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.tax_rate'       => 'required|numeric|min:0|max:100',
        ], [
            'supplierId.required'         => 'Selecciona el proveedor.',
            'issuedAt.required'           => 'La fecha de emisión es requerida.',
            'items.*.description.required'=> 'Cada partida requiere una descripción.',
            'items.*.quantity.min'        => 'La cantidad debe ser mayor a cero.',
        ]);

        try {
            DB::transaction(function () {
                $companyId = auth()->user()->company_id;

                $folio = 'NCP-' . str_pad(
                    SupplierCreditNote::where('company_id', $companyId)->count() + 1,
                    6, '0', STR_PAD_LEFT
                );

                $cn = SupplierCreditNote::create([
                    'company_id'                 => $companyId,
                    'purchase_invoice_id'        => $this->invoiceId ?: null,
                    'supplier_id'                => $this->supplierId,
                    'created_by'                 => auth()->id(),
                    'folio'                      => $folio,
                    'supplier_credit_note_number'=> $this->supplierCreditNoteNumber ?: null,
                    'currency'                   => $this->currency,
                    'subtotal'                   => $this->subtotal,
                    'tax'                        => $this->taxAmount,
                    'total'                      => $this->total,
                    'applied_amount'             => 0,
                    'status'                     => 'draft',
                    'reason'                     => $this->reason,
                    'notes'                      => $this->notes ?: null,
                    'issued_at'                  => $this->issuedAt,
                ]);

                foreach ($this->items as $item) {
                    SupplierCreditNoteItem::create([
                        'supplier_credit_note_id' => $cn->id,
                        'product_id'              => $item['product_id'] ?: null,
                        'description'             => $item['description'],
                        'quantity'                => $item['quantity'],
                        'unit_price'              => $item['unit_price'],
                        'tax_rate'                => $item['tax_rate'],
                        'subtotal'                => $item['subtotal'],
                    ]);
                }

                session()->flash('success', "Nota de crédito {$folio} registrada.");
                $this->redirectRoute('purchases.credit-notes.show', $cn, navigate: true);
            });

        } catch (\Throwable $e) {
            Log::error('SupplierCreditNoteForm save', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchases.supplier-credit-note-form', [
            'subtotal'  => $this->subtotal,
            'taxAmount' => $this->taxAmount,
            'total'     => $this->total,
        ]);
    }
}
