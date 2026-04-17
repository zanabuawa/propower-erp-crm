<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceDailyBalance;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BankStatement extends Component
{
    public ?int    $accountId  = null;
    public string  $dateFrom   = '';
    public string  $dateTo     = '';
    public string  $view       = 'statement'; // statement | daily

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();

        // Pre-seleccionar primera cuenta activa
        $first = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->first();
        if ($first) $this->accountId = $first->id;
    }

    public function updatedAccountId(): void  { $this->rebuildDailyBalances(); }
    public function updatedDateFrom(): void   { $this->rebuildDailyBalances(); }
    public function updatedDateTo(): void     { $this->rebuildDailyBalances(); }

    // ── Reconstruir saldos diarios para el rango ─────────────────────────
    public function rebuildDailyBalances(): void
    {
        if (! $this->accountId || ! $this->dateFrom || ! $this->dateTo) return;

        $account = FinanceAccount::find($this->accountId);
        if (! $account) return;

        $start = Carbon::parse($this->dateFrom)->startOfDay();
        $end   = Carbon::parse($this->dateTo)->endOfDay();

        // Saldo antes del período (base de apertura)
        $priorBalance = (float) FinanceTransaction::where('account_id', $this->accountId)
            ->where('status', 'confirmado')
            ->where('transaction_date', '<', $start->toDateString())
            ->selectRaw("SUM(CASE WHEN type='ingreso' THEN amount ELSE 0 END)
                        - SUM(CASE WHEN type='egreso' THEN amount ELSE 0 END) as net")
            ->value('net') + (float) $account->opening_balance;

        $current = $priorBalance;
        $date    = $start->copy();

        while ($date->lte($end)) {
            $ds = $date->toDateString();

            $txDay = FinanceTransaction::where('account_id', $this->accountId)
                ->where('status', 'confirmado')
                ->whereDate('transaction_date', $ds)
                ->get(['type', 'amount']);

            $income  = $txDay->where('type', 'ingreso')->sum('amount');
            $expense = $txDay->where('type', 'egreso')->sum('amount');
            $closing = $current + $income - $expense;

            FinanceDailyBalance::updateOrCreate(
                ['finance_account_id' => $this->accountId, 'balance_date' => $ds],
                [
                    'opening_balance'   => $current,
                    'total_income'      => $income,
                    'total_expense'     => $expense,
                    'closing_balance'   => $closing,
                    'transaction_count' => $txDay->count(),
                ]
            );

            $current = $closing;
            $date->addDay();
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $accounts = FinanceAccount::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();

        $account = $this->accountId ? FinanceAccount::find($this->accountId) : null;

        $transactions = collect();
        $dailyBalances = collect();
        $summary = ['income' => 0, 'expense' => 0, 'opening' => 0, 'closing' => 0];

        if ($account && $this->dateFrom && $this->dateTo) {
            $transactions = FinanceTransaction::where('account_id', $this->accountId)
                ->where('status', 'confirmado')
                ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            $dailyBalances = FinanceDailyBalance::where('finance_account_id', $this->accountId)
                ->whereBetween('balance_date', [$this->dateFrom, $this->dateTo])
                ->orderBy('balance_date')
                ->get();

            if ($dailyBalances->isEmpty()) {
                $this->rebuildDailyBalances();
                $dailyBalances = FinanceDailyBalance::where('finance_account_id', $this->accountId)
                    ->whereBetween('balance_date', [$this->dateFrom, $this->dateTo])
                    ->orderBy('balance_date')
                    ->get();
            }

            $summary['income']  = (float) $transactions->where('type', 'ingreso')->sum('amount');
            $summary['expense'] = (float) $transactions->where('type', 'egreso')->sum('amount');
            $summary['opening'] = (float) ($dailyBalances->first()?->opening_balance ?? $account->opening_balance);
            $summary['closing'] = (float) ($dailyBalances->last()?->closing_balance ?? $account->current_balance);

            // Agregar saldo acumulado a cada transacción
            $running = $summary['opening'];
            $transactions = $transactions->map(function ($tx) use (&$running) {
                $running += $tx->type === 'ingreso' ? (float) $tx->amount : -(float) $tx->amount;
                $tx->running_balance = $running;
                return $tx;
            });
        }

        return view('livewire.finance.bank-statement', compact(
            'accounts', 'account', 'transactions', 'dailyBalances', 'summary'
        ));
    }
}
