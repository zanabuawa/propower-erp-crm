<?php

namespace App\Livewire\Sales;

use App\Models\SaleQuotation;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class QuotationShow extends Component
{
    public SaleQuotation $quotation;

    public function mount($quotation): void
    {
        $this->quotation = $quotation instanceof SaleQuotation
            ? $quotation
            : SaleQuotation::with(['items.product', 'customer', 'createdBy', 'priceList', 'order'])->findOrFail($quotation);
    }

    public function markAsSent(): void
    {
        $this->quotation->update(['status' => 'sent']);
        $this->quotation->refresh();
        session()->flash('success', 'Cotización marcada como enviada.');
    }

    public function accept(): void
    {
        $this->quotation->update(['status' => 'accepted']);
        $this->quotation->refresh();
        session()->flash('success', 'Cotización aceptada.');
    }

    public function reject(): void
    {
        $this->quotation->update(['status' => 'rejected']);
        $this->quotation->refresh();
        session()->flash('success', 'Cotización rechazada.');
    }

    public function render()
    {
        return view('livewire.sales.quotation-show');
    }
}