<?php

namespace App\Observers;

use App\Models\FinanceCashflow;
use App\Models\ProjectMilestone;

class ProjectMilestoneObserver
{
    public function created(ProjectMilestone $milestone): void
    {
        // Disparador: nuevo hito con monto de pago → crear cashflow proyectado de entrada
        if ($milestone->payment_amount > 0) {
            $this->createProjectedCashflow($milestone);
        }
    }

    public function updated(ProjectMilestone $milestone): void
    {
        // Disparador: hito completado → marcar su cashflow como realizado
        if ($milestone->wasChanged('status') && $milestone->status === 'completado') {
            $this->markCashflowRealized($milestone);
        }

        // Disparador: monto de pago cambió → actualizar cashflow existente
        if ($milestone->wasChanged('payment_amount') && $milestone->payment_amount > 0) {
            FinanceCashflow::where('reference', 'HITO-' . $milestone->id)
                ->where('is_realized', false)
                ->update(['amount' => $milestone->payment_amount]);
        }
    }

    private function createProjectedCashflow(ProjectMilestone $milestone): void
    {
        $project = $milestone->project;
        if (!$project) {
            return;
        }

        // Evitar duplicados
        if (FinanceCashflow::where('reference', 'HITO-' . $milestone->id)->exists()) {
            return;
        }

        // Buscar cuenta de la empresa vinculada al proyecto
        $account = \App\Models\FinanceAccount::whereHas('company', function ($q) use ($project) {
            $q->whereHas('branches', function ($q2) use ($project) {
                $q2->where('id', $project->branch_id);
            });
        })
        ->where('is_active', true)
        ->orderBy('id')
        ->first();

        // Si no hay cuenta vinculada por sucursal, buscar cualquier activa del auth
        if (!$account) {
            $account = \App\Models\FinanceAccount::where('is_active', true)->orderBy('id')->first();
        }

        if (!$account) {
            return;
        }

        FinanceCashflow::create([
            'account_id'    => $account->id,
            'project_id'    => $project->id,
            'concept'       => 'Cobro por hito: ' . $milestone->name,
            'type'          => 'proyectado',
            'flow'          => 'entrada',
            'category'      => 'operacion',
            'amount'        => $milestone->payment_amount,
            'currency'      => $project->currency ?? 'MXN',
            'expected_date' => $milestone->due_date ?? now(),
            'is_realized'   => false,
            'reference'     => 'HITO-' . $milestone->id,
            'notes'         => 'Generado automáticamente al crear hito del proyecto #' . $project->id,
        ]);
    }

    private function markCashflowRealized(ProjectMilestone $milestone): void
    {
        FinanceCashflow::where('reference', 'HITO-' . $milestone->id)
            ->where('is_realized', false)
            ->update([
                'is_realized'   => true,
                'realized_date' => now()->toDateString(),
                'type'          => 'real',
            ]);
    }
}
