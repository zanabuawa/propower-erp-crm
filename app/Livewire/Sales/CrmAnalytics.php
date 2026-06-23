<?php

namespace App\Livewire\Sales;

use App\Models\SalesOpportunity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CrmAnalytics extends Component
{
    public string $filterFrom = '';
    public string $filterTo   = '';
    public string $filterUser = '';

    public function mount(): void
    {
        $this->filterFrom = now()->startOfYear()->format('Y-m-d');
        $this->filterTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $base = SalesOpportunity::where('company_id', $companyId)
            ->when($this->filterFrom, fn($q) => $q->where('created_at', '>=', $this->filterFrom))
            ->when($this->filterTo,   fn($q) => $q->where('created_at', '<=', $this->filterTo . ' 23:59:59'))
            ->when($this->filterUser, fn($q) => $q->where('assigned_to', $this->filterUser));

        $all = (clone $base)->get();

        // ── Pipeline por etapa ────────────────────────────────────────────
        $stages = SalesOpportunity::STAGES;
        $stageData = collect($stages)->map(function ($label, $key) use ($all) {
            $rows = $all->where('stage', $key);
            return [
                'key'      => $key,
                'label'    => $label,
                'count'    => $rows->count(),
                'value'    => (float) $rows->sum('estimated_value'),
                'weighted' => (float) $rows->sum(fn($o) => $o->weightedValue()),
            ];
        })->values();

        $activeStages = $stageData->whereNotIn('key', ['won', 'lost']);
        $maxPipelineValue = $activeStages->max('value') ?: 1;

        // ── KPIs globales ─────────────────────────────────────────────────
        $total      = $all->count();
        $won        = $all->where('stage', 'won');
        $lost       = $all->where('stage', 'lost');
        $active     = $all->whereNotIn('stage', ['won', 'lost']);

        $winRate    = ($won->count() + $lost->count()) > 0
            ? round($won->count() / ($won->count() + $lost->count()) * 100, 1)
            : null;

        $wonValue   = (float) $won->sum('estimated_value');
        $lostValue  = (float) $lost->sum('estimated_value');
        $pipelineValue = (float) $active->sum('estimated_value');

        // Avg days to close (won)
        $avgDaysToClose = $won->filter(fn($o) => $o->won_at && $o->created_at)
            ->map(fn($o) => $o->created_at->diffInDays($o->won_at))
            ->avg();

        // ── Razones de pérdida ────────────────────────────────────────────
        $lostReasons = $lost->groupBy('lost_reason')
            ->map(fn($rows, $key) => [
                'reason' => SalesOpportunity::LOST_REASONS[$key] ?? $key ?? 'Sin especificar',
                'count'  => $rows->count(),
                'value'  => (float) $rows->sum('estimated_value'),
            ])->sortByDesc('count')->values();

        // ── Por ejecutivo ─────────────────────────────────────────────────
        $byUser = $all->groupBy('assigned_to')->map(function ($rows) {
            $w = $rows->where('stage', 'won');
            $l = $rows->where('stage', 'lost');
            $wr = ($w->count() + $l->count()) > 0
                ? round($w->count() / ($w->count() + $l->count()) * 100, 1)
                : null;
            return [
                'name'     => $rows->first()->assignedTo?->name ?? 'Sin asignar',
                'total'    => $rows->count(),
                'won'      => $w->count(),
                'lost'     => $l->count(),
                'active'   => $rows->whereNotIn('stage', ['won', 'lost'])->count(),
                'value'    => (float) $w->sum('estimated_value'),
                'win_rate' => $wr,
            ];
        })->sortByDesc('value')->values();

        // ── Tendencia mensual (12 meses) ──────────────────────────────────
        $monthlyTrend = collect(range(11, 0))->map(function ($i) use ($companyId) {
            $d = now()->subMonths($i);
            $rows = SalesOpportunity::where('company_id', $companyId)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->get();
            $wonRows = SalesOpportunity::where('company_id', $companyId)
                ->where('stage', 'won')
                ->whereYear('won_at', $d->year)
                ->whereMonth('won_at', $d->month)
                ->get();
            return [
                'label'   => $d->translatedFormat('M'),
                'new'     => $rows->count(),
                'won'     => $wonRows->count(),
                'won_val' => (float) $wonRows->sum('estimated_value'),
            ];
        });

        $maxMonthVal = $monthlyTrend->max('won_val') ?: 1;

        // ── Próximas a cerrar ─────────────────────────────────────────────
        $closingSoon = SalesOpportunity::where('company_id', $companyId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->whereNotNull('expected_close_date')
            ->where('expected_close_date', '<=', now()->addDays(30))
            ->orderBy('expected_close_date')
            ->with(['customer', 'assignedTo'])
            ->limit(8)
            ->get();

        // ── Usuarios para filtro ──────────────────────────────────────────
        $users = DB::table('users')
            ->join('sales_opportunities', 'users.id', '=', 'sales_opportunities.assigned_to')
            ->where('sales_opportunities.company_id', $companyId)
            ->groupBy('users.id', 'users.name')
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        return view('livewire.sales.crm-analytics', compact(
            'stageData', 'activeStages', 'maxPipelineValue',
            'total', 'winRate', 'wonValue', 'lostValue', 'pipelineValue',
            'avgDaysToClose', 'lostReasons', 'byUser',
            'monthlyTrend', 'maxMonthVal', 'closingSoon', 'users',
        ));
    }
}
