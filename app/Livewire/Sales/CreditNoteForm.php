<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\SaleCreditNote;
use App\Models\SaleCreditNoteItem;
use App\Models\SaleInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CreditNoteForm extends Component
{
    // Header
    public ?int    $invoiceId  = null;
    public ?int    $customerId = null;
    public string  $currency   = 'MXN';
    public string  $reason     = '';

    // Items
    public array $items = [];

    // UI state
    public array $invoiceOptions  = [];
    public array $customerOptions = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;

        $this->customerOptions = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->invoiceOptions = SaleInvoice::where('company_id', $companyId)
            ->whereIn('status', ['stamped', 'draft'])
            ->with('customer')
            ->latest()
            ->get(['id', 'folio', 'customer_id', 'currency', 'total'])
            ->toArray();

        // Pre-seleccionar factura si viene por query string ?invoice=
        $invoiceId = request()->query('invoice');
        if ($invoiceId) {
            $inv = collect($this->invoiceOptions)->firstWhere('id', (int) $invoiceId);
            if ($inv) {
                $this->invoiceId  = $inv['id'];
                $this->customerId = $inv['customer_id'];
                $this->currency   = $inv['currency'];
            }
        }

        $this->addItem();
    }

    public function updatedInvoiceId(): void
    {
        if (! $this->invoiceId) return;
        $inv = collect($this->invoiceOptions)->firstWhere('id', (int) $this->invoiceId);
        if ($inv) {
            $this->customerId = $inv['customer_id'];
            $this->currency   = $inv['currency'];
        }
    }

    // ── Items ────────────────────────────────────────────────────────────
    public function addItem(): void
    {
        $this->items[] = [
            'description' => '',
            'quantity'    => 1,
            'unit_price'  => 0,
            'tax_rate'    => 16,
            'subtotal'    => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) return;
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, ?string $key = null): void
    {
        if (!$key) return;
        [$index] = explode('.', $key);
        if (!isset($this->items[(int) $index])) return;
        $this->recalcItem((int) $index);
    }

    private function recalcItem(int $index): void
    {
        $item = $this->items[$index];
        $qty  = max(0, (float) ($item['quantity']   ?? 0));
        $prc  = max(0, (float) ($item['unit_price']  ?? 0));
        $this->items[$index]['subtotal'] = round($qty * $prc, 2);
    }

    // ── Computed totals ──────────────────────────────────────────────────
    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn($i) => (float) ($i['subtotal'] ?? 0));
    }

    public function getTaxProperty(): float
    {
        return collect($this->items)->sum(function ($i) {
            $subtotal = (float) ($i['subtotal'] ?? 0);
            $rate     = (float) ($i['tax_rate']  ?? 0);
            return round($subtotal * $rate / 100, 2);
        });
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->tax, 2);
    }

    // ── Save ─────────────────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'invoiceId'           => 'required|exists:sale_invoices,id',
            'customerId'          => 'required|exists:customers,id',
            'reason'              => 'required|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ], [
            'invoiceId.required'           => 'Selecciona una factura.',
            'customerId.required'          => 'El cliente es requerido.',
            'reason.required'              => 'El motivo es requerido.',
            'items.*.description.required' => 'La descripción de cada partida es requerida.',
            'items.*.quantity.min'         => 'La cantidad debe ser mayor a cero.',
            'items.*.unit_price.min'       => 'El precio no puede ser negativo.',
        ]);

        try {
            DB::transaction(function () {
                $companyId = auth()->user()->company_id;

                $folio = 'NC-' . str_pad(
                    SaleCreditNote::where('company_id', $companyId)->count() + 1,
                    6, '0', STR_PAD_LEFT
                );

                $note = SaleCreditNote::create([
                    'company_id'      => $companyId,
                    'sale_invoice_id' => $this->invoiceId,
                    'customer_id'     => $this->customerId,
                    'created_by'      => auth()->id(),
                    'folio'           => $folio,
                    'currency'        => $this->currency,
                    'status'          => 'draft',
                    'reason'          => $this->reason,
                    'subtotal'        => $this->subtotal,
                    'tax'             => $this->tax,
                    'total'           => $this->total,
                ]);

                foreach ($this->items as $item) {
                    SaleCreditNoteItem::create([
                        'sale_credit_note_id' => $note->id,
                        'description'         => $item['description'],
                        'quantity'            => $item['quantity'],
                        'unit_price'          => $item['unit_price'],
                        'tax_rate'            => $item['tax_rate'],
                        'subtotal'            => $item['subtotal'],
                    ]);
                }

                session()->flash('success', "Nota de crédito {$folio} creada correctamente.");
                $this->redirectRoute('sales.credit-notes.show', $note, navigate: true);
            });
        } catch (\Throwable $e) {
            Log::error('CreditNoteForm save error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sales.credit-note-form', [
            'subtotal' => $this->subtotal,
            'tax'      => $this->tax,
            'total'    => $this->total,
        ]);
    }
}
