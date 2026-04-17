<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinancePeriodClose;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PeriodCloseIndex extends Component
{
    public ?int $viewingId  = null;  // periodo activo en panel
    public string $activeTab = 'checklist';

    // Modal cierre / reapertura
    public bool   $showCloseModal  = false;
    public bool   $showReopenModal = false;
    public string $closeNotes      = '';
    public ?int   $actionId        = null;

    // ── Inicializar o abrir período ────────────────────────────────────────
    public function openPeriod(int $year, int $month): void
    {
        $companyId = auth()->user()->company_id;

        $period = FinancePeriodClose::firstOrCreate(
            ['company_id' => $companyId, 'year' => $year, 'month' => $month],
            [
                'period_label' => Carbon::createFromDate($year, $month, 1)
                                    ->locale('es')->isoFormat('MMMM YYYY'),
                'status'       => 'open',
                'checklist'    => FinancePeriodClose::defaultChecklist(),
            ]
        );

        // Auto-calcular totales del período
        $this->recalcPeriod($period);
        $period->refresh();

        $this->viewingId = $period->id;
        $this->activeTab = 'checklist';
    }

    private function recalcPeriod(FinancePeriodClose $period): void
    {
        $companyId = auth()->user()->company_id;

        $start = Carbon::createFromDate($period->year, $period->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $base = FinanceTransaction::where('company_id', auth()->user()->company_id ?? 0)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'confirmado');

        // Buscar por account que pertenezca a la empresa
        $txBase = FinanceTransaction::whereHas('account', fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'confirmado');

        $income  = (float)(clone $txBase)->where('type', 'ingreso')->sum('amount');
        $expense = (float)(clone $txBase)->where('type', 'egreso')->sum('amount');

        // Saldo de apertura: suma del opening_balance de todas las cuentas activas
        $openingCash = (float) FinanceAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->sum('opening_balance');

        $period->update([
            'total_income'  => $income,
            'total_expense' => $expense,
            'net_result'    => $income - $expense,
            'opening_cash'  => $openingCash,
            'closing_cash'  => (float) FinanceAccount::where('company_id', $companyId)
                                    ->where('is_active', true)->sum('current_balance'),
        ]);
    }

    // ── Checklist ─────────────────────────────────────────────────────────
    public function toggleChecklistItem(int $periodId, string $key): void
    {
        $period = FinancePeriodClose::findOrFail($periodId);
        if ($period->status === 'closed') return;

        $checklist = $period->checklist ?? [];
        foreach ($checklist as &$item) {
            if ($item['key'] === $key) {
                $item['done']    = ! $item['done'];
                $item['done_at'] = $item['done'] ? now()->toDateTimeString() : null;
                $item['done_by'] = $item['done'] ? auth()->id() : null;
                break;
            }
        }

        $status = collect($checklist)->every(fn($i) => $i['done']) ? 'reviewing' : 'open';
        $period->update(['checklist' => $checklist, 'status' => $status]);
        $period->refresh();

        // Refresca el viewing
        $this->viewingId = $period->id;
    }

    // ── Cerrar período ────────────────────────────────────────────────────
    public function openCloseModal(int $id): void
    {
        $this->actionId       = $id;
        $this->closeNotes     = '';
        $this->showCloseModal = true;
    }

    public function closePeriod(): void
    {
        $period = FinancePeriodClose::findOrFail($this->actionId);

        if (! $period->can_close) {
            session()->flash('error', 'Completa todos los elementos del checklist antes de cerrar.');
            $this->showCloseModal = false;
            return;
        }

        $period->update([
            'status'    => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'notes'     => trim(($period->notes ?? '') . "\n" . $this->closeNotes),
        ]);

        $this->showCloseModal = false;
        $this->closeNotes     = '';
        session()->flash('success', "Período {$period->period_label} cerrado.");
    }

    // ── Reabrir período ───────────────────────────────────────────────────
    public function openReopenModal(int $id): void
    {
        $this->actionId        = $id;
        $this->showReopenModal = true;
    }

    public function reopenPeriod(): void
    {
        $period = FinancePeriodClose::findOrFail($this->actionId);
        $period->update([
            'status'       => 'reviewing',
            'reopened_by'  => auth()->id(),
            'reopened_at'  => now(),
        ]);

        $this->showReopenModal = false;
        session()->flash('success', "Período {$period->period_label} reabierto para revisión.");
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Últimos 12 meses + mes actual
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $months->push([
                'year'  => (int) $d->year,
                'month' => (int) $d->month,
                'label' => $d->locale('es')->isoFormat('MMMM YYYY'),
            ]);
        }

        // Períodos existentes
        $periods = FinancePeriodClose::where('company_id', $companyId)
            ->orderByDesc('year')->orderByDesc('month')
            ->get()->keyBy(fn($p) => $p->year . '-' . $p->month);

        $viewing = $this->viewingId
            ? FinancePeriodClose::find($this->viewingId)
            : null;

        return view('livewire.finance.period-close-index', compact('months', 'periods', 'viewing'));
    }
}
