<?php

namespace App\Observers;

use App\Models\FinanceBudget;
use App\Models\FinanceTransaction;

class FinanceTransactionObserver
{
    public function saved(FinanceTransaction $transaction): void
    {
        $this->updateAccountBalance($transaction);
        $this->updateBudgetActual($transaction);
    }

    public function deleted(FinanceTransaction $transaction): void
    {
        $this->updateAccountBalance($transaction);
        $this->updateBudgetActual($transaction);
    }

    private function updateAccountBalance(FinanceTransaction $transaction): void
    {
        $account = $transaction->account;
        if (!$account) {
            return;
        }

        // Recalcular saldo actual: saldo inicial + ingresos confirmados - egresos confirmados
        $ingresos = $account->transactions()
            ->where('type', 'ingreso')
            ->where('status', 'confirmado')
            ->sum('amount');

        $egresos = $account->transactions()
            ->where('type', 'egreso')
            ->where('status', 'confirmado')
            ->sum('amount');

        // Transferencias: restar de cuenta origen, sumar en cuenta destino
        $transferenciasSalida = $account->transactions()
            ->where('type', 'transferencia')
            ->where('status', 'confirmado')
            ->sum('amount');

        $transferenciaEntrada = \App\Models\FinanceTransaction::where('transfer_to_account_id', $account->id)
            ->where('type', 'transferencia')
            ->where('status', 'confirmado')
            ->sum('amount');

        $balance = (float) $account->opening_balance
            + (float) $ingresos
            - (float) $egresos
            - (float) $transferenciasSalida
            + (float) $transferenciaEntrada;

        $account->updateQuietly(['current_balance' => round($balance, 2)]);
    }

    private function updateBudgetActual(FinanceTransaction $transaction): void
    {
        if (!$transaction->project_id) {
            return;
        }

        // Actualizar amount_actual de presupuestos de tipo 'proyecto' ligados a este proyecto
        $budgets = FinanceBudget::where('project_id', $transaction->project_id)
            ->where('status', '!=', 'cerrado')
            ->get();

        foreach ($budgets as $budget) {
            $this->recalculateBudgetActual($budget);
        }
    }

    private function recalculateBudgetActual(FinanceBudget $budget): void
    {
        if (!$budget->project_id) {
            return;
        }

        $egresos = \App\Models\FinanceTransaction::where('project_id', $budget->project_id)
            ->where('type', 'egreso')
            ->where('status', 'confirmado')
            ->sum('amount');

        if ((float) $budget->amount_actual !== (float) $egresos) {
            $budget->updateQuietly(['amount_actual' => $egresos]);
        }
    }
}
