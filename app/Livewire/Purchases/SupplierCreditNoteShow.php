<?php

namespace App\Livewire\Purchases;

use App\Models\SupplierCreditNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierCreditNoteShow extends Component
{
    public SupplierCreditNote $creditNote;

    public bool   $showApplyModal  = false;
    public string $applyAmount     = '';
    public string $applyNotes      = '';
    public string $applyError      = '';

    public bool   $showCancelModal = false;

    public function mount(SupplierCreditNote $creditNote): void
    {
        $this->creditNote = $creditNote->load([
            'items.product',
            'invoice.supplier',
            'supplier',
            'createdBy',
        ]);

        $this->applyAmount = (string) $this->creditNote->balance;
    }

    // ── Aplicar NC a la factura ───────────────────────────────────────────
    public function openApplyModal(): void
    {
        $this->applyError  = '';
        $this->applyAmount = (string) $this->creditNote->balance;
        $this->showApplyModal = true;
    }

    public function apply(): void
    {
        $this->applyError = '';

        $this->validate([
            'applyAmount' => 'required|numeric|min:0.01',
        ], [
            'applyAmount.required' => 'El monto es obligatorio.',
            'applyAmount.min'      => 'El monto debe ser mayor a cero.',
        ]);

        if ((float) $this->applyAmount > $this->creditNote->balance + 0.01) {
            $this->applyError = 'El monto excede el saldo disponible ($' .
                number_format($this->creditNote->balance, 2) . ').';
            return;
        }

        try {
            DB::transaction(function () {
                $amount = (float) $this->applyAmount;

                // Reducir saldo de la factura vinculada
                if ($this->creditNote->invoice) {
                    $inv         = $this->creditNote->invoice;
                    $newBalance  = max(0, (float) $inv->total - (float) $inv->paid_amount - $amount);
                    $newPaid     = (float) $inv->total - $newBalance;

                    $newStatus = match(true) {
                        $newPaid >= (float) $inv->total => 'paid',
                        $newPaid > 0                   => 'partial',
                        default                        => $inv->status,
                    };

                    $inv->update([
                        'paid_amount' => $newPaid,
                        'status'      => $newStatus,
                        'paid_at'     => $newStatus === 'paid' ? now() : null,
                    ]);
                }

                $newApplied = (float) $this->creditNote->applied_amount + $amount;
                $newStatus  = $newApplied >= (float) $this->creditNote->total - 0.01
                    ? 'applied'
                    : 'partial';

                $notes = trim(($this->creditNote->notes ?? '') . "\n" . ($this->applyNotes ?? ''));

                $this->creditNote->update([
                    'applied_amount' => $newApplied,
                    'status'         => $newStatus,
                    'applied_at'     => now(),
                    'notes'          => $notes ?: null,
                ]);
            });

            $this->showApplyModal = false;
            $this->applyNotes     = '';
            $this->creditNote->refresh()->load(['items.product', 'invoice', 'supplier', 'createdBy']);
            $this->applyAmount = (string) $this->creditNote->balance;
            session()->flash('success', 'Nota de crédito aplicada correctamente.');

        } catch (\Throwable $e) {
            Log::error('SupplierCreditNoteShow apply', ['error' => $e->getMessage()]);
            $this->applyError = 'Error al aplicar: ' . $e->getMessage();
        }
    }

    // ── Cancelar ─────────────────────────────────────────────────────────
    public function cancel(): void
    {
        if ($this->creditNote->applied_amount > 0) {
            session()->flash('error', 'No se puede cancelar una nota con importes ya aplicados.');
            $this->showCancelModal = false;
            return;
        }

        $this->creditNote->update(['status' => 'cancelled']);
        $this->creditNote->refresh();
        $this->showCancelModal = false;
        session()->flash('success', 'Nota de crédito cancelada.');
    }

    public function render()
    {
        return view('livewire.purchases.supplier-credit-note-show');
    }
}
