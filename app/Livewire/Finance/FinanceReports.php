<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceBudget;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class FinanceReports extends Component
{
    public string $activeTab  = 'transactions';
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $accountId  = '';
    public string $type       = '';
    public string $category   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    private function accountIds(): Collection
    {
        $companyId = auth()->user()->company_id;
        $q = FinanceAccount::where('company_id', $companyId)->where('is_active', true);
        if ($this->accountId) $q->where('id', $this->accountId);
        return $q->pluck('id');
    }

    // ── Exportar CSV ──────────────────────────────────────────────────────
    public function exportCsv(): StreamedResponse
    {
        $rows = $this->getTransactions();

        return response()->streamDownload(function () use ($rows) {
            $fp = fopen('php://output', 'w');
            fputcsv($fp, ['Fecha', 'Folio', 'Concepto', 'Categoría', 'Tipo', 'Monto', 'Moneda', 'Cuenta', 'Referencia', 'Estado']);
            foreach ($rows as $tx) {
                fputcsv($fp, [
                    $tx->transaction_date->format('d/m/Y'),
                    $tx->folio,
                    $tx->concept,
                    $tx->category,
                    $tx->type,
                    number_format($tx->amount, 2),
                    $tx->currency,
                    $tx->account?->name,
                    $tx->reference,
                    $tx->status,
                ]);
            }
            fclose($fp);
        }, 'reporte_transacciones_' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ── Datos ─────────────────────────────────────────────────────────────
    private function getTransactions(): Collection
    {
        $ids = $this->accountIds();

        $q = FinanceTransaction::whereIn('account_id', $ids)
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->with('account:id,name,currency')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if ($this->type)     $q->where('type', $this->type);
        if ($this->category) $q->where('category', $this->category);

        return $q->get();
    }

    private function getMonthlyBreakdown(): array
    {
        $ids   = $this->accountIds();
        $from  = Carbon::parse($this->dateFrom)->startOfMonth();
        $to    = Carbon::parse($this->dateTo)->endOfMonth();
        $months = [];

        while ($from->lte($to)) {
            $mFrom = $from->copy()->startOfMonth()->toDateString();
            $mTo   = $from->copy()->endOfMonth()->toDateString();

            $q = FinanceTransaction::whereIn('account_id', $ids)
                ->where('status', 'confirmado')
                ->whereBetween('transaction_date', [$mFrom, $mTo]);

            if ($this->category) $q->where('category', $this->category);

            $inc = (clone $q)->where('type', 'ingreso')->sum('amount');
            $exp = (clone $q)->where('type', 'egreso')->sum('amount');

            $months[] = [
                'label'   => ucfirst($from->isoFormat('MMMM YYYY')),
                'income'  => (float) $inc,
                'expense' => (float) $exp,
                'net'     => (float) $inc - (float) $exp,
            ];

            $from->addMonth();
        }

        return $months;
    }

    private function getCategoryBreakdown(): array
    {
        $ids = $this->accountIds();

        $q = FinanceTransaction::whereIn('account_id', $ids)
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->where('status', 'confirmado')
            ->select('category', 'type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category', 'type')
            ->orderByDesc('total');

        if ($this->type) $q->where('type', $this->type);

        return $q->get()->toArray();
    }

    private function getBudgetComparison(): array
    {
        $companyId = auth()->user()->company_id;
        $year      = Carbon::parse($this->dateFrom)->year;

        $budgets = FinanceBudget::where('company_id', $companyId)
            ->where('year', $year)
            ->orderBy('category')
            ->get();

        $ids    = $this->accountIds();
        $result = [];

        foreach ($budgets as $budget) {
            $actual = FinanceTransaction::whereIn('account_id', $ids)
                ->where('status', 'confirmado')
                ->where('category', $budget->category)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            $planned = (float) $budget->amount_planned;
            $result[] = [
                'name'         => $budget->name,
                'category'     => $budget->category,
                'period_type'  => $budget->period_type,
                'planned'      => $planned,
                'actual'       => (float) $actual,
                'variance'     => (float) $actual - $planned,
                'variance_pct' => $planned > 0 ? round((($actual - $planned) / $planned) * 100, 1) : null,
                'progress_pct' => $planned > 0 ? min(100, round(($actual / $planned) * 100)) : 0,
            ];
        }

        return $result;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $accounts = FinanceAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'currency']);

        // Categorías disponibles (del enum/fillable de FinanceTransaction)
        $categories = FinanceTransaction::whereIn('account_id', $this->accountIds())
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        $transactions     = collect();
        $monthlyBreakdown = [];
        $categoryBreakdown = [];
        $budgetComparison  = [];

        $totals = ['income' => 0, 'expense' => 0, 'net' => 0, 'count' => 0];

        if ($this->activeTab === 'transactions') {
            $transactions = $this->getTransactions();
            $totals['income']  = $transactions->where('type', 'ingreso')->sum('amount');
            $totals['expense'] = $transactions->where('type', 'egreso')->sum('amount');
            $totals['net']     = $totals['income'] - $totals['expense'];
            $totals['count']   = $transactions->count();
        } elseif ($this->activeTab === 'monthly') {
            $monthlyBreakdown = $this->getMonthlyBreakdown();
        } elseif ($this->activeTab === 'categories') {
            $categoryBreakdown = $this->getCategoryBreakdown();
        } elseif ($this->activeTab === 'budget') {
            $budgetComparison = $this->getBudgetComparison();
        }

        return view('livewire.finance.finance-reports', compact(
            'accounts', 'categories',
            'transactions', 'totals',
            'monthlyBreakdown', 'categoryBreakdown', 'budgetComparison'
        ));
    }
}
