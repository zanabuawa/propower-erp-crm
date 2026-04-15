<?php

namespace App\Observers;

use App\Models\FinanceCashflow;

class FinanceCashflowObserver
{
    public function saved(FinanceCashflow $cashflow): void
    {
        $this->updateBudgetActual($cashflow);
    }

    public function deleted(FinanceCashflow $cashflow): void
    {
        $this->updateBudgetActual($cashflow);
    }

    private function updateBudgetActual(FinanceCashflow $cashflow): void
    {
        if (!$cashflow->budget_id) {
            return;
        }

        $budget = $cashflow->budget;
        if (!$budget || $budget->status === 'cerrado') {
            return;
        }

        // Sumar todos los cashflows realizados (salida) ligados al presupuesto
        $totalRealizado = \App\Models\FinanceCashflow::where('budget_id', $budget->id)
            ->where('is_realized', true)
            ->where('flow', 'salida')
            ->sum('amount');

        if ((float) $budget->amount_actual !== (float) $totalRealizado) {
            $budget->updateQuietly(['amount_actual' => $totalRealizado]);
        }
    }
}
