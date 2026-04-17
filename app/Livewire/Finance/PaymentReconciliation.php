<?php

namespace App\Livewire\Finance;

use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PaymentReconciliation extends Component
{
    use WithPagination;

    // ── Filtros ──────────────────────────────────────────────────────────
    public string $tab          = 'pending';   // pending | reconciled
    public string $search       = '';
    public ?int   $filterAccount  = null;
    public ?int   $filterCustomer = null;
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';

    // ── Conciliación individual ──────────────────────────────────────────
    public ?int  $reconcilingId   = null;
    public string $reconcilingNote = '';

    // ── Conciliación en lote ─────────────────────────────────────────────
    public array $selectedIds    = [];
    public bool  $showBatchModal = false;
    public string $batchNote     = '';

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingTab(): void            { $this->resetPage(); $this->selectedIds = []; }
    public function updatingFilterAccount(): void  { $this->resetPage(); }
    public function updatingFilterCustomer(): void { $this->resetPage(); }
    public function updatingFilterDateFrom(): void { $this->resetPage(); }
    public function updatingFilterDateTo(): void   { $this->resetPage(); }

    // ── Base query ───────────────────────────────────────────────────────
    private function baseQuery()
    {
        return SalePayment::with(['customer:id,name', 'invoice:id,folio', 'financeAccount:id,name'])
            ->where('company_id', auth()->user()->company_id)
            ->where('status', 'applied')
            ->when($this->tab === 'pending',
                fn($q) => $q->whereNull('reconciled_at'))
            ->when($this->tab === 'reconciled',
                fn($q) => $q->whereNotNull('reconciled_at'))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('reference', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) =>
                        $c->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('invoice', fn($i) =>
                        $i->where('folio', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterAccount,  fn($q) => $q->where('finance_account_id', $this->filterAccount))
            ->when($this->filterCustomer, fn($q) => $q->where('customer_id', $this->filterCustomer))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('paid_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,   fn($q) => $q->whereDate('paid_at', '<=', $this->filterDateTo))
            ->orderByDesc('paid_at');
    }

    // ── KPIs ─────────────────────────────────────────────────────────────
    private function kpis(): array
    {
        $base = SalePayment::where('company_id', auth()->user()->company_id)
            ->where('status', 'applied');

        $totalApplied       = (float) (clone $base)->sum('amount');
        $totalReconciled    = (float) (clone $base)->whereNotNull('reconciled_at')->sum('amount');
        $totalPending       = $totalApplied - $totalReconciled;
        $countPending       = (clone $base)->whereNull('reconciled_at')->count();
        $countReconciled    = (clone $base)->whereNotNull('reconciled_at')->count();
        $reconciliationRate = $totalApplied > 0
            ? round($totalReconciled / $totalApplied * 100, 1)
            : 0;

        return compact(
            'totalApplied', 'totalReconciled', 'totalPending',
            'countPending', 'countReconciled', 'reconciliationRate'
        );
    }

    // ── Conciliar individual ─────────────────────────────────────────────
    public function openReconcile(int $id): void
    {
        $this->reconcilingId   = $id;
        $this->reconcilingNote = '';
    }

    public function confirmReconcile(): void
    {
        if (! $this->reconcilingId) return;

        try {
            SalePayment::where('id', $this->reconcilingId)
                ->where('company_id', auth()->user()->company_id)
                ->whereNull('reconciled_at')
                ->update([
                    'reconciled_at'      => now(),
                    'reconciled_by'      => auth()->id(),
                    'reconciliation_note' => $this->reconcilingNote ?: null,
                ]);

            $this->reconcilingId   = null;
            $this->reconcilingNote = '';
            session()->flash('success', 'Pago conciliado correctamente.');

        } catch (\Throwable $e) {
            Log::error('PaymentReconciliation confirmReconcile', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al conciliar: ' . $e->getMessage());
        }
    }

    public function cancelReconcile(): void
    {
        $this->reconcilingId   = null;
        $this->reconcilingNote = '';
    }

    // ── Revertir conciliación ────────────────────────────────────────────
    public function revert(int $id): void
    {
        try {
            SalePayment::where('id', $id)
                ->where('company_id', auth()->user()->company_id)
                ->whereNotNull('reconciled_at')
                ->update([
                    'reconciled_at'      => null,
                    'reconciled_by'      => null,
                    'reconciliation_note' => null,
                ]);

            session()->flash('success', 'Conciliación revertida.');

        } catch (\Throwable $e) {
            Log::error('PaymentReconciliation revert', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al revertir: ' . $e->getMessage());
        }
    }

    // ── Conciliación en lote ─────────────────────────────────────────────
    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function selectAll(): void
    {
        $this->selectedIds = $this->baseQuery()->pluck('id')->all();
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    public function openBatchModal(): void
    {
        if (empty($this->selectedIds)) return;
        $this->batchNote     = '';
        $this->showBatchModal = true;
    }

    public function confirmBatch(): void
    {
        if (empty($this->selectedIds)) {
            $this->showBatchModal = false;
            return;
        }

        try {
            SalePayment::whereIn('id', $this->selectedIds)
                ->where('company_id', auth()->user()->company_id)
                ->whereNull('reconciled_at')
                ->update([
                    'reconciled_at'      => now(),
                    'reconciled_by'      => auth()->id(),
                    'reconciliation_note' => $this->batchNote ?: null,
                ]);

            $count = count($this->selectedIds);
            $this->selectedIds    = [];
            $this->showBatchModal = false;
            $this->batchNote      = '';
            session()->flash('success', "{$count} pago(s) conciliados correctamente.");

        } catch (\Throwable $e) {
            Log::error('PaymentReconciliation confirmBatch', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al conciliar en lote: ' . $e->getMessage());
        }
    }

    // ── Render ────────────────────────────────────────────────────────────
    public function render()
    {
        $payments  = $this->baseQuery()->paginate(20);
        $kpis      = $this->kpis();
        $accounts  = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);
        $customers = Customer::where('company_id', auth()->user()->company_id)
            ->orderBy('name')->get(['id', 'name']);

        // Totales del filtro actual (sin paginar)
        $filteredTotal = (float) $this->baseQuery()->sum('amount');

        return view('livewire.finance.payment-reconciliation', compact(
            'payments', 'kpis', 'accounts', 'customers', 'filteredTotal'
        ));
    }
}
