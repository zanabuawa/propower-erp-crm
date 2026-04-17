<?php

namespace App\Livewire\Sales;

use App\Models\SaleCreditNote;
use App\Models\SaleInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CreditNoteShow extends Component
{
    public SaleCreditNote $creditNote;

    public bool   $showApplyModal  = false;
    public bool   $showCancelModal = false;
    public string $applyError      = '';
    public string $cancelError     = '';

    public function mount(SaleCreditNote $creditNote): void
    {
        $this->creditNote = $creditNote->load([
            'items', 'customer', 'invoice', 'createdBy',
        ]);
    }

    // ── Aplicar nota de crédito a la factura ─────────────────────────────
    public function apply(): void
    {
        $this->applyError = '';

        $note    = $this->creditNote;
        $invoice = $note->invoice;

        if ($note->status !== 'draft') {
            $this->applyError = 'Solo las notas en borrador pueden aplicarse.';
            return;
        }

        if (! $invoice) {
            $this->applyError = 'No se encontró la factura relacionada.';
            return;
        }

        $balance = $invoice->total - $invoice->paid_amount;

        if ($balance <= 0) {
            $this->applyError = 'La factura ya está totalmente pagada.';
            return;
        }

        $creditAmount = min((float) $note->total, $balance);

        try {
            DB::transaction(function () use ($note, $invoice, $creditAmount) {
                $newPaidAmount = (float) $invoice->paid_amount + $creditAmount;
                $newStatus     = $newPaidAmount >= (float) $invoice->total ? 'paid' : $invoice->status;

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'status'      => $newStatus,
                ]);

                $note->update(['status' => 'applied']);
            });

            $this->creditNote->refresh()->load(['items', 'customer', 'invoice', 'createdBy']);
            $this->showApplyModal = false;
            session()->flash('success', 'Nota de crédito aplicada correctamente a la factura.');

        } catch (\Throwable $e) {
            Log::error('CreditNoteShow apply error', ['id' => $this->creditNote->id, 'error' => $e->getMessage()]);
            $this->applyError = 'Error al aplicar: ' . $e->getMessage();
        }
    }

    // ── Cancelar nota de crédito ─────────────────────────────────────────
    public function cancel(): void
    {
        $this->cancelError = '';

        $note = $this->creditNote;

        if ($note->status === 'cancelled') {
            $this->cancelError = 'Esta nota ya está cancelada.';
            return;
        }

        if ($note->status === 'applied') {
            $this->cancelError = 'No es posible cancelar una nota ya aplicada. Revierte manualmente el pago si es necesario.';
            return;
        }

        try {
            $note->update(['status' => 'cancelled']);
            $this->creditNote->refresh()->load(['items', 'customer', 'invoice', 'createdBy']);
            $this->showCancelModal = false;
            session()->flash('success', 'Nota de crédito cancelada.');

        } catch (\Throwable $e) {
            Log::error('CreditNoteShow cancel error', ['id' => $this->creditNote->id, 'error' => $e->getMessage()]);
            $this->cancelError = 'Error al cancelar: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.sales.credit-note-show');
    }
}
