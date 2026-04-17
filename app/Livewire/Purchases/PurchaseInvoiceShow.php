<?php

namespace App\Livewire\Purchases;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\PurchaseInvoice;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PurchaseInvoiceShow extends Component
{
    public PurchaseInvoice $invoice;
    public string $activeTab = 'match';

    // Pago
    public bool   $showPaymentForm    = false;
    public string $paymentAmount      = '';
    public string $paymentMethod      = 'transfer';
    public string $paymentReference   = '';
    public string $paymentNotes       = '';
    public ?int   $paymentAccountId   = null;
    public array  $financeAccounts    = [];
    public string $paymentError       = '';

    // Aprobación manual
    public bool   $showApproveModal   = false;
    public string $approveNote        = '';

    // Confirmación de pago con discrepancias
    public bool   $bypassMatchBlock   = false;

    // Cancelar
    public bool   $showCancelModal    = false;

    public function mount(PurchaseInvoice $invoice): void
    {
        $this->invoice = $invoice->load([
            'items.orderItem.product',
            'order.receipts.items',
            'supplier',
            'createdBy',
            'payments.financeAccount',
            'payments.createdBy',
        ]);

        $this->paymentAmount = $this->invoice->balance;

        $this->financeAccounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'type', 'currency', 'current_balance'])
            ->toArray();
    }

    // ── 3-Way Match ───────────────────────────────────────────────────────
    public function runMatch(): void
    {
        try {
            $this->invoice->load(['items', 'order.receipts.items']);
            $this->invoice->runThreeWayMatch();
            $this->invoice->refresh()->load([
                'items.orderItem.product',
                'order.receipts.items',
                'supplier',
                'createdBy',
                'payments.financeAccount',
            ]);
            session()->flash('success', 'Cotejo 3-way ejecutado: ' .
                PurchaseInvoice::MATCH_STATUS[$this->invoice->match_status]);
        } catch (\Throwable $e) {
            Log::error('PurchaseInvoiceShow runMatch', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error en el cotejo: ' . $e->getMessage());
        }
    }

    // ── Aprobación manual ─────────────────────────────────────────────────
    public function approveManually(): void
    {
        $this->invoice->update([
            'status' => $this->invoice->status === 'pending' ? 'approved' : $this->invoice->status,
            'notes'  => trim(($this->invoice->notes ?? '') . "\n[Aprobada manualmente] " . $this->approveNote),
        ]);

        // Auto-run 3-way match and then override to 'approved' so payment is unblocked
        try {
            $this->invoice->load(['items', 'order.receipts.items']);
            $this->invoice->runThreeWayMatch();
            $this->invoice->update(['match_status' => 'approved']);
        } catch (\Throwable $e) {
            Log::error('PurchaseInvoiceShow approveManually match', ['error' => $e->getMessage()]);
        }

        $this->invoice->refresh()->load(['items.orderItem.product', 'supplier', 'createdBy', 'payments.financeAccount']);
        $this->showApproveModal = false;
        $this->approveNote      = '';
        session()->flash('success', 'Factura aprobada manualmente. Cotejo 3-way actualizado.');
    }

    // ── Pago ──────────────────────────────────────────────────────────────
    public function openPaymentForm(): void
    {
        $this->activeTab       = 'payments';
        $this->showPaymentForm = true;
        $this->paymentError    = '';
        $this->paymentAmount   = $this->invoice->balance;
    }

    public function savePayment(): void
    {
        $this->paymentError = '';

        // Block payment when discrepancies exist unless user explicitly confirmed
        if ($this->invoice->match_status === 'discrepancy' && ! $this->bypassMatchBlock) {
            $this->paymentError = 'Esta factura tiene discrepancias en el cotejo 3-way. Revisa el cotejo o marca "Pagar de todas formas" para continuar.';
            return;
        }

        $this->validate([
            'paymentAmount'    => 'required|numeric|min:0.01',
            'paymentMethod'    => 'required|in:transfer,check,cash,credit_card',
            'paymentAccountId' => 'required|integer|exists:finance_accounts,id',
        ], [
            'paymentAmount.required'    => 'El monto es obligatorio.',
            'paymentAmount.min'         => 'El monto debe ser mayor a cero.',
            'paymentAccountId.required' => 'Selecciona la cuenta de pago.',
        ]);

        if ((float) $this->paymentAmount > $this->invoice->balance + 0.01) {
            $this->paymentError = 'El monto excede el saldo pendiente ($' . number_format($this->invoice->balance, 2) . ').';
            return;
        }

        try {
            DB::transaction(function () {
                $companyId = auth()->user()->company_id;

                $folio = 'PP-' . str_pad(
                    SupplierPayment::where('company_id', $companyId)->count() + 1,
                    6, '0', STR_PAD_LEFT
                );

                SupplierPayment::create([
                    'company_id'         => $companyId,
                    'purchase_invoice_id'=> $this->invoice->id,
                    'supplier_id'        => $this->invoice->supplier_id,
                    'created_by'         => auth()->id(),
                    'finance_account_id' => $this->paymentAccountId,
                    'folio'              => $folio,
                    'currency'           => $this->invoice->currency,
                    'payment_method'     => $this->paymentMethod,
                    'status'             => 'applied',
                    'amount'             => $this->paymentAmount,
                    'reference'          => $this->paymentReference ?: null,
                    'notes'              => $this->paymentNotes ?: null,
                    'paid_at'            => now(),
                ]);

                // Registrar egreso en finance_transactions
                FinanceTransaction::create([
                    'account_id'       => $this->paymentAccountId,
                    'registered_by'    => auth()->id(),
                    'folio'            => 'TXN-' . $folio,
                    'type'             => 'egreso',
                    'concept'          => 'Pago proveedor: ' . $this->invoice->supplier->name .
                                         ' — Factura ' . $this->invoice->folio .
                                         ' (' . $this->invoice->supplier_invoice_number . ')',
                    'category'         => 'compra',
                    'amount'           => $this->paymentAmount,
                    'currency'         => $this->invoice->currency,
                    'exchange_rate'    => 1,
                    'transaction_date' => now()->toDateString(),
                    'reference'        => $folio,
                    'status'           => 'confirmado',
                    'notes'            => $this->paymentNotes ?: null,
                ]);

                $newPaidAmount = (float) $this->invoice->paid_amount + (float) $this->paymentAmount;
                $newStatus = match(true) {
                    $newPaidAmount >= (float) $this->invoice->total => 'paid',
                    $newPaidAmount > 0                             => 'partial',
                    default                                        => $this->invoice->status,
                };

                $this->invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'status'      => $newStatus,
                    'paid_at'     => $newStatus === 'paid' ? now() : null,
                ]);

                // Propagar a la OC: actualizar paid_amount y marcar como pagada si cubre el total
                if ($this->invoice->purchase_order_id) {
                    $purchaseOrder = \App\Models\PurchaseOrder::find($this->invoice->purchase_order_id);
                    if ($purchaseOrder) {
                        $orderPaid = $purchaseOrder->paid_amount + (float) $this->paymentAmount;
                        $orderPaid = min($orderPaid, (float) $purchaseOrder->total);
                        $orderStatus = $orderPaid >= (float) $purchaseOrder->total
                            ? 'paid'
                            : $purchaseOrder->status;
                        $purchaseOrder->update([
                            'paid_amount' => $orderPaid,
                            'status'      => $orderStatus,
                        ]);
                    }
                }
            });

            $this->showPaymentForm = false;
            $this->reset(['paymentMethod', 'paymentReference', 'paymentNotes', 'paymentAccountId']);
            $this->invoice->refresh()->load([
                'items.orderItem.product',
                'supplier',
                'createdBy',
                'payments.financeAccount',
                'payments.createdBy',
            ]);
            $this->paymentAmount = $this->invoice->balance;
            session()->flash('success', 'Pago registrado correctamente.');

        } catch (\Throwable $e) {
            Log::error('PurchaseInvoiceShow savePayment', ['error' => $e->getMessage()]);
            $this->paymentError = 'Error al registrar el pago: ' . $e->getMessage();
        }
    }

    // ── Cancelar ─────────────────────────────────────────────────────────
    public function cancel(): void
    {
        if ($this->invoice->paid_amount > 0) {
            session()->flash('error', 'No se puede cancelar una factura con pagos aplicados.');
            $this->showCancelModal = false;
            return;
        }

        $this->invoice->update(['status' => 'cancelled']);
        $this->invoice->refresh();
        $this->showCancelModal = false;
        session()->flash('success', 'Factura cancelada.');
    }

    public function render()
    {
        return view('livewire.purchases.purchase-invoice-show');
    }
}
