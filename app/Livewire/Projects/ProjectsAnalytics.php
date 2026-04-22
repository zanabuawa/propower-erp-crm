<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectsAnalytics extends Component
{
    public string $filterStatus = '';
    public string $filterType   = '';
    public string $filterFrom   = '';
    public string $filterTo     = '';

    public function mount(): void
    {
        $this->filterFrom = now()->startOfYear()->format('Y-m-d');
        $this->filterTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        // ── Aggregated cost data via efficient DB queries ──────────────────
        $expenseTotals = DB::table('project_expenses')
            ->where('status', '!=', 'rechazado')
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->selectRaw('project_id, SUM(amount) as total')
            ->pluck('total', 'project_id');

        $materialTotals = DB::table('project_materials')
            ->whereIn('status', ['usado', 'solicitado', 'comprado'])
            ->groupBy('project_id')
            ->selectRaw('project_id, SUM(COALESCE(quantity_used, quantity_needed) * COALESCE(unit_cost, 0)) as total')
            ->pluck('total', 'project_id');

        $laborTotals = DB::table('hr_attendances')
            ->join('project_employees', function ($j) {
                $j->on('hr_attendances.employee_id', '=', 'project_employees.employee_id')
                  ->on('hr_attendances.project_id',  '=', 'project_employees.project_id');
            })
            ->whereNotNull('hr_attendances.project_id')
            ->whereNotNull('hr_attendances.worked_hours')
            ->groupBy('hr_attendances.project_id')
            ->selectRaw('hr_attendances.project_id, SUM(hr_attendances.worked_hours * COALESCE(project_employees.cost_per_hour,0)) as total')
            ->pluck('total', 'project_id');

        // Fallback labor from pivot when no attendance
        $laborFromPivot = DB::table('project_employees')
            ->groupBy('project_id')
            ->selectRaw('project_id, SUM(COALESCE(hours_assigned,0) * COALESCE(cost_per_hour,0)) as total')
            ->pluck('total', 'project_id');

        $hoursAssigned = DB::table('project_employees')
            ->groupBy('project_id')
            ->selectRaw('project_id, SUM(COALESCE(hours_assigned,0)) as total')
            ->pluck('total', 'project_id');

        $hoursReal = DB::table('hr_attendances')
            ->whereNotNull('project_id')
            ->whereNotNull('worked_hours')
            ->groupBy('project_id')
            ->selectRaw('project_id, SUM(worked_hours) as total')
            ->pluck('total', 'project_id');

        // ── Load projects ─────────────────────────────────────────────────
        $query = Project::with(['customer', 'saleOrder'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterFrom,   fn($q) => $q->where('start_date', '>=', $this->filterFrom))
            ->when($this->filterTo,     fn($q) => $q->where('start_date', '<=', $this->filterTo))
            ->whereNotIn('status', ['borrador'])
            ->orderByDesc('id');

        $projects = $query->get()->map(function ($p) use (
            $expenseTotals, $materialTotals, $laborTotals, $laborFromPivot,
            $hoursAssigned, $hoursReal
        ) {
            $expCost   = (float) ($expenseTotals[$p->id]  ?? 0);
            $matCost   = (float) ($materialTotals[$p->id] ?? 0);
            $labReal   = (float) ($laborTotals[$p->id]    ?? 0);
            $labPivot  = (float) ($laborFromPivot[$p->id] ?? 0);
            $labCost   = $labReal > 0 ? $labReal : $labPivot;
            $totalCost = $expCost + $matCost + $labCost;

            $revenue   = (float) ($p->revenue_amount > 0 ? $p->revenue_amount : ($p->saleOrder?->total ?? 0));
            $profit    = $revenue - $totalCost;
            $margin    = $revenue > 0 ? ($profit / $revenue) * 100 : null;

            $budgetPct = $p->budget > 0 ? ($totalCost / $p->budget) * 100 : null;
            $physPct   = (float) ($p->progress ?? 0);

            $hAssigned = (float) ($hoursAssigned[$p->id] ?? 0);
            $hReal     = (float) ($hoursReal[$p->id]     ?? 0);
            $efficiency = $hAssigned > 0 ? ($hReal / $hAssigned) * 100 : null;

            return [
                'id'          => $p->id,
                'code'        => $p->code,
                'name'        => $p->name,
                'status'      => $p->status,
                'type'        => $p->type,
                'customer'    => $p->customer?->name ?? '—',
                'customer_id' => $p->customer_id,
                'budget'      => (float) ($p->budget ?? 0),
                'revenue'     => $revenue,
                'total_cost'  => $totalCost,
                'labor_cost'  => $labCost,
                'material_cost' => $matCost,
                'expense_cost'  => $expCost,
                'profit'      => $profit,
                'margin'      => $margin,
                'budget_pct'  => $budgetPct,
                'phys_pct'    => $physPct,
                'gap'         => $budgetPct !== null ? $physPct - $budgetPct : null,
                'h_assigned'  => $hAssigned,
                'h_real'      => $hReal,
                'efficiency'  => $efficiency,
                'over_budget' => $budgetPct !== null && $budgetPct > 100,
                'start_date'  => $p->start_date,
                'end_date'    => $p->end_date,
            ];
        });

        // ── KPIs globales ─────────────────────────────────────────────────
        $kpis = [
            'total'        => $projects->count(),
            'with_revenue' => $projects->where('revenue', '>', 0)->count(),
            'profitable'   => $projects->filter(fn($p) => $p['margin'] !== null && $p['margin'] > 0)->count(),
            'over_budget'  => $projects->where('over_budget', true)->count(),
            'total_revenue'=> $projects->sum('revenue'),
            'total_cost'   => $projects->sum('total_cost'),
            'total_profit' => $projects->sum('profit'),
            'avg_margin'   => $projects->where('margin', '!==', null)->avg('margin'),
        ];

        // ── Por cliente ───────────────────────────────────────────────────
        $byClient = $projects->groupBy('customer_id')->map(function ($rows) {
            $revenue    = $rows->sum('revenue');
            $cost       = $rows->sum('total_cost');
            $profit     = $revenue - $cost;
            return [
                'customer'  => $rows->first()['customer'],
                'projects'  => $rows->count(),
                'revenue'   => $revenue,
                'cost'      => $cost,
                'profit'    => $profit,
                'margin'    => $revenue > 0 ? ($profit / $revenue) * 100 : null,
            ];
        })->sortByDesc('profit')->values();

        // ── Por tipo ──────────────────────────────────────────────────────
        $typeLabels = [
            'obra'          => 'Obra',
            'mantenimiento' => 'Mantenimiento',
            'instalacion'   => 'Instalación',
            'servicio'      => 'Servicio',
            'consultoria'   => 'Consultoría',
            'otro'          => 'Otro',
        ];

        $byType = $projects->groupBy('type')->map(function ($rows, $type) use ($typeLabels) {
            $revenue = $rows->sum('revenue');
            $cost    = $rows->sum('total_cost');
            $profit  = $revenue - $cost;
            return [
                'type'     => $typeLabels[$type] ?? $type,
                'projects' => $rows->count(),
                'revenue'  => $revenue,
                'cost'     => $cost,
                'profit'   => $profit,
                'margin'   => $revenue > 0 ? ($profit / $revenue) * 100 : null,
            ];
        })->sortByDesc('profit')->values();

        // ── Por estado ────────────────────────────────────────────────────
        $byStatus = $projects->groupBy('status')->map(fn($rows, $s) => [
            'status'   => $s,
            'count'    => $rows->count(),
            'cost'     => $rows->sum('total_cost'),
            'revenue'  => $rows->sum('revenue'),
        ])->values();

        // ── Top rentables y peores ────────────────────────────────────────
        $ranked    = $projects->where('revenue', '>', 0)->sortByDesc('margin')->values();
        $topBest   = $ranked->take(5);
        $topWorst  = $ranked->sortBy('margin')->take(5);

        return view('livewire.projects.project-analytics', compact(
            'projects', 'kpis', 'byClient', 'byType', 'byStatus',
            'topBest', 'topWorst', 'typeLabels',
        ));
    }
}
