<?php

namespace App\Livewire\Sales;

use App\Models\SaleInvoice;
use App\Models\SalePayment;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InvoiceShow extends Component
{
    public SaleInvoice $invoice;
    public string $activeTab = 'items';
    public bool $showPaymentForm = false;
    public string $paymentAmount = '';
    public string $paymentMethod = 'cash';
    public string $paymentReference = '';
    public string $paymentNotes = '';

    public function mount($invoice): void
    {
        $this->invoice = $invoice instanceof SaleInvoice
            ? $invoice
            : SaleInvoice::with(['items.product', 'customer', 'createdBy', 'payments', 'order'])->findOrFail($invoice);

        $this->paymentAmount = $this->invoice->balance;
    }

    public function markAsStamped(): void
    {
        $this->invoice->update(['status' => 'stamped']);
        $this->invoice->refresh();
        session()->flash('success', 'Factura marcada como timbrada.');
    }

    public function savePayment(): void
    {
        $this->validate([
            'paymentAmount'    => 'required|numeric|min:0.01',
            'paymentMethod'    => 'required|in:cash,transfer,card,check,credit',
        ]);

        $folio = 'PAG-' . str_pad(
            SalePayment::where('company_id', auth()->user()->company_id)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        SalePayment::create([
            'company_id'      => auth()->user()->company_id,
            'sale_invoice_id' => $this->invoice->id,
            'customer_id'     => $this->invoice->customer_id,
            'created_by'      => auth()->id(),
            'folio'           => $folio,
            'currency'        => $this->invoice->currency,
            'payment_method'  => $this->paymentMethod,
            'status'          => 'applied',
            'amount'          => $this->paymentAmount,
            'reference'       => $this->paymentReference ?: null,
            'notes'           => $this->paymentNotes ?: null,
            'paid_at'         => now(),
        ]);

        $newPaidAmount = $this->invoice->paid_amount + $this->paymentAmount;
        $newStatus     = $newPaidAmount >= $this->invoice->total ? 'paid' : $this->invoice->status;

        $this->invoice->update([
            'paid_amount' => $newPaidAmount,
            'status'      => $newStatus,
        ]);

        $this->reset(['paymentAmount', 'paymentMethod', 'paymentReference', 'paymentNotes', 'showPaymentForm']);
        $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);
        session()->flash('success', 'Pago registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.sales.invoice-show');
    }
}