<?php

namespace App\Observers;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\ProjectExpense;

class ProjectExpenseObserver
{
    public function saved(ProjectExpense $expense): void
    {
        $this->recalculateCost($expense);

        // Disparador: gasto aprobado → crear transacción financiera automáticamente
        if (
            $expense->wasChanged('status') &&
            $expense->status === 'aprobado' &&
            !FinanceTransaction::where('reference', 'GTO-' . $expense->id)->exists()
        ) {
            $this->createFinanceTransaction($expense);
        }
    }

    public function deleted(ProjectExpense $expense): void
    {
        $this->recalculateCost($expense);
    }

    private function recalculateCost(ProjectExpense $expense): void
    {
        $project = $expense->project;
        if (!$project) {
            return;
        }

        $total = $project->expenses()
            ->whereNotIn('status', ['rechazado'])
            ->sum('amount');

        if ((float) $project->cost_actual !== (float) $total) {
            $project->updateQuietly(['cost_actual' => $total]);
        }
    }

    private function createFinanceTransaction(ProjectExpense $expense): void
    {
        // Buscar cuenta principal activa de la empresa del proyecto
        $account = FinanceAccount::where('company_id', optional($expense->project->branch)->company_id ?? auth()->user()?->company_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (!$account) {
            return;
        }

        FinanceTransaction::create([
            'account_id'       => $account->id,
            'project_id'       => $expense->project_id,
            'registered_by'    => $expense->registered_by ?? auth()->id(),
            'folio'            => 'TXN-GTO-' . strtoupper(uniqid()),
            'type'             => 'egreso',
            'concept'          => 'Gasto de proyecto: ' . $expense->concept,
            'category'         => 'proyecto',
            'amount'           => $expense->amount,
            'currency'         => $expense->currency,
            'exchange_rate'    => 1,
            'transaction_date' => $expense->expense_date,
            'reference'        => 'GTO-' . $expense->id,
            'status'           => 'confirmado',
            'notes'            => 'Generado automáticamente al aprobar gasto #' . $expense->id,
        ]);
    }
}
