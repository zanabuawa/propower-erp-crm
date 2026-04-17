<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SupplierPaymentReconciliation extends Component
{
    use WithPagination;

    public string $activeTab       = 'pending';
    public string $search          = '';
    public string $filterAccount   = '';
    public string $filterDateFrom  = '';
    public string $filterDateTo    = '';

    // Conciliación individual
    public bool   $showConfirmModal    = false;
    public ?int   $confirmPaymentId    = null;
    public string $reconciliationNote  = '';

    // Conciliación masiva
    public array  $selected            = [];
    public bool   $showBatchModal      = false;
    public string $batchNote           = '';

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingActiveTab(): void    { $this->resetPage(); $this->selected = []; }
    public function updatingFilterAccount(): void { $this->resetPage(); }

    // ── Conciliar individual ──────────────────────────────────────────────
    public function openConfirm(int $paymentId): void
    {
        $this->confirmPaymentId   = $paymentId;
        $this->reconciliationNote = '';
        $this->showConfirmModal   = true;
    }

    public function confirmReconcile(): void
    {
        try {
            SupplierPayment::where('id', $this->confirmPaymentId)
                ->where('company_id', auth()->user()->company_id)
                ->update([
                    'reconciled_at'       => now(),
                    'reconciled_by'       => auth()->id(),
                    'reconciliation_note' => $this->reconciliationNote ?: null,
                ]);

            $this->showConfirmModal   = false;
            $this->confirmPaymentId   = null;
            $this->reconciliationNote = '';
            session()->flash('success', 'Pago conciliado.');
        } catch (\Throwable $e) {
            Log::error('SupplierPaymentReconciliation confirmReconcile', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    // ── Revertir conciliación ─────────────────────────────────────────────
    public function revert(int $paymentId): void
    {
        SupplierPayment::where('id', $paymentId)
            ->where('company_id', auth()->user()->company_id)
            ->update([
                'reconciled_at'       => null,
                'reconciled_by'       => null,
                'reconciliation_note' => null,
            ]);
        session()->flash('success', 'Conciliación revertida.');
    }

    // ── Conciliación masiva ───────────────────────────────────────────────
    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_filter($this->selected, fn($s) => $s !== $id));
        } else {
            $this->selected[] = $id;
        }
    }

    public function openBatchModal(): void
    {
        if (empty($this->selected)) return;
        $this->batchNote      = '';
        $this->showBatchModal = true;
    }

    public function confirmBatch(): void
    {
        try {
            DB::table('supplier_payments')
                ->whereIn('id', $this->selected)
                ->where('company_id', auth()->user()->company_id)
                ->whereNull('reconciled_at')
                ->update([
                    'reconciled_at'       => now(),
                    'reconciled_by'       => auth()->id(),
                    'reconciliation_note' => $this->batchNote ?: null,
                    'updated_at'          => now(),
                ]);

            $count = count($this->selected);
            $this->selected       = [];
            $this->showBatchModal = false;
            $this->batchNote      = '';
            session()->flash('success', "{$count} pagos conciliados.");
        } catch (\Throwable $e) {
            Log::error('SupplierPaymentReconciliation confirmBatch', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $base = SupplierPayment::with(['invoice.supplier', 'financeAccount', 'createdBy', 'reconciledBy'])
            ->where('company_id', $companyId)
            ->where('status', 'applied')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('reference', 'like', "%{$this->search}%")
                  ->orWhereHas('invoice', fn($i) =>
                        $i->whereHas('supplier', fn($s) =>
                            $s->where('name', 'like', "%{$this->search}%")));
            }))
            ->when($this->filterAccount, fn($q) => $q->where('finance_account_id', $this->filterAccount))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('paid_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,   fn($q) => $q->whereDate('paid_at', '<=', $this->filterDateTo));

        $payments = (clone $base)
            ->when($this->activeTab === 'pending',
                fn($q) => $q->whereNull('reconciled_at'),
                fn($q) => $q->whereNotNull('reconciled_at'))
            ->latest('paid_at')
            ->paginate(25);

        $accounts = FinanceAccount::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // KPIs
        $kpis = [
            'total_applied'    => (float)(clone $base)->sum('amount'),
            'total_reconciled' => (float)(clone $base)->whereNotNull('reconciled_at')->sum('amount'),
            'total_pending'    => (float)(clone $base)->whereNull('reconciled_at')->sum('amount'),
            'count_pending'    => (clone $base)->whereNull('reconciled_at')->count(),
        ];
        $kpis['rate'] = $kpis['total_applied'] > 0
            ? round($kpis['total_reconciled'] / $kpis['total_applied'] * 100, 1)
            : 0;

        return view('livewire.finance.supplier-payment-reconciliation', compact('payments', 'accounts', 'kpis'));
    }
}
