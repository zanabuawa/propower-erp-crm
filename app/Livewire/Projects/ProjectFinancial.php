<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectBudgetLine;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectFinancial extends Component
{
    public Project $project;

    public string $revenueInput = '';
    public bool $editingRevenue = false;

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->revenueInput = $project->revenue_amount > 0
            ? number_format($project->revenue_amount, 2, '.', '')
            : '';
    }

    public function saveRevenue(): void
    {
        $value = str_replace(',', '', $this->revenueInput);
        $this->validate(['revenueInput' => 'nullable|numeric|min:0']);

        $this->project->update(['revenue_amount' => $value ?: null]);
        $this->project->refresh();
        $this->editingRevenue = false;
        session()->flash('success', 'Ingreso actualizado.');
    }

    public function render()
    {
        $project = $this->project->load([
            'employees', 'materials', 'expenses', 'saleOrder', 'budgetVersions.lines',
        ]);

        // ── Labor ──────────────────────────────────────────────────────────
        $laborRows = $project->employees()
            ->withPivot('hours_assigned', 'cost_per_hour', 'role', 'is_active')
            ->get()
            ->map(fn($e) => [
                'name'      => $e->full_name,
                'role'      => $e->pivot->role,
                'hours'     => (float) ($e->pivot->hours_assigned ?? 0),
                'rate'      => (float) ($e->pivot->cost_per_hour ?? 0),
                'cost'      => (float) ($e->pivot->hours_assigned ?? 0) * (float) ($e->pivot->cost_per_hour ?? 0),
                'is_active' => $e->pivot->is_active,
            ])->sortByDesc('cost')->values();

        $totalLabor = $laborRows->sum('cost');

        // ── Attendance-based labor (real logged hours) ─────────────────────
        $attendanceLabor = \App\Models\HrAttendance::with('employee')
            ->where('project_id', $project->id)
            ->whereNotNull('worked_hours')
            ->get();

        $attendanceByEmployee = $attendanceLabor->groupBy('employee_id')->map(function ($rows) use ($project) {
            $emp  = $rows->first()->employee;
            $hours = (float) $rows->sum('worked_hours');
            $rate  = (float) ($emp?->projects()
                ->where('projects.id', $project->id)
                ->first()?->pivot->cost_per_hour ?? 0);
            return [
                'name'  => $emp?->full_name ?? '—',
                'hours' => $hours,
                'rate'  => $rate,
                'cost'  => $hours * $rate,
            ];
        })->values();

        $totalLaborReal = $attendanceByEmployee->sum('cost');

        // ── Materials ──────────────────────────────────────────────────────
        $materialRows = $project->materials->map(fn($m) => [
            'name'     => $m->name,
            'qty'      => (float) ($m->quantity_used ?: $m->quantity_needed),
            'unit'     => $m->unit,
            'unit_cost'=> (float) ($m->unit_cost ?? 0),
            'cost'     => (float) ($m->quantity_used ?: $m->quantity_needed) * (float) ($m->unit_cost ?? 0),
            'status'   => $m->status,
        ])->sortByDesc('cost')->values();

        $totalMaterials = $materialRows->sum('cost');

        // ── Expenses ──────────────────────────────────────────────────────
        $expenseRows = $project->expenses->where('status', '!=', 'rechazado')
            ->groupBy('category')
            ->map(fn($rows, $cat) => [
                'category' => $cat,
                'count'    => $rows->count(),
                'cost'     => (float) $rows->sum('amount'),
            ])->sortByDesc('cost')->values();

        $totalExpenses = $expenseRows->sum('cost');

        // ── Consolidation ─────────────────────────────────────────────────
        // Use attendance-based labor when available, fall back to assigned hours
        $realLaborCost = $totalLaborReal > 0 ? $totalLaborReal : $totalLabor;
        $totalCost     = $realLaborCost + $totalMaterials + $totalExpenses;

        // ── Budget comparison ─────────────────────────────────────────────
        $activeVersion = $project->budgetVersions->firstWhere('status', 'vigente')
            ?? $project->budgetVersions->first();

        $budgetByCategory = [];
        if ($activeVersion) {
            $budgetByCategory = $activeVersion->lines
                ->groupBy('category')
                ->map(fn($lines) => (float) $lines->sum('budgeted_amount'))
                ->toArray();
        }

        $costByCategory = [
            'material'     => $totalMaterials,
            'mano_obra'    => $realLaborCost,
            'viaticos'     => (float) $project->expenses->where('status', '!=', 'rechazado')
                ->whereIn('category', ['viaticos', 'transporte', 'viaje'])->sum('amount'),
            'subcontrato'  => (float) $project->expenses->where('status', '!=', 'rechazado')
                ->where('category', 'subcontrato')->sum('amount'),
            'indirectos'   => (float) $project->expenses->where('status', '!=', 'rechazado')
                ->where('category', 'indirecto')->sum('amount'),
            'otros'        => (float) $project->expenses->where('status', '!=', 'rechazado')
                ->whereIn('category', ['otro', 'herramienta', 'equipo'])->sum('amount'),
        ];

        $categoryLabels = ProjectBudgetLine::$categoryLabels;

        $comparisonRows = collect($categoryLabels)->map(fn($label, $key) => [
            'key'       => $key,
            'label'     => $label,
            'budgeted'  => $budgetByCategory[$key] ?? 0,
            'executed'  => $costByCategory[$key] ?? 0,
            'variance'  => ($budgetByCategory[$key] ?? 0) - ($costByCategory[$key] ?? 0),
            'pct'       => ($budgetByCategory[$key] ?? 0) > 0
                ? min(round(($costByCategory[$key] ?? 0) / ($budgetByCategory[$key] ?? 1) * 100, 1), 999)
                : null,
        ])->values();

        // ── Revenue & Margin ──────────────────────────────────────────────
        $revenue    = $project->effective_revenue;
        $profit     = $revenue - $totalCost;
        $marginPct  = $revenue > 0 ? ($profit / $revenue) * 100 : null;
        $budgetTotal = $activeVersion?->total ?? (float) ($project->budget ?? 0);

        // ── Physical vs Financial Progress ────────────────────────────────
        $physPct    = (float) ($project->progress ?? 0);
        $financPct  = $budgetTotal > 0 ? min(round($totalCost / $budgetTotal * 100, 1), 999) : null;
        $progressGap = $financPct !== null ? round($physPct - $financPct, 1) : null;

        // ── Resource Efficiency ───────────────────────────────────────────
        $hoursAssigned = (float) $project->employees()
            ->withPivot('hours_assigned', 'cost_per_hour', 'is_active')
            ->get()
            ->sum(fn($e) => $e->pivot->hours_assigned ?? 0);

        $hoursReal = (float) \App\Models\HrAttendance::where('project_id', $project->id)
            ->whereNotNull('worked_hours')
            ->sum('worked_hours');

        $efficiency = $hoursAssigned > 0 ? round($hoursReal / $hoursAssigned * 100, 1) : null;

        // ── Phase/milestone cost breakdown ────────────────────────────────
        $milestoneRows = $project->milestones->map(function ($m) use ($project) {
            $taskIds = $project->tasks->where('milestone_id', $m->id)->pluck('id');
            return [
                'name'   => $m->name,
                'status' => $m->status,
                'tasks'  => $taskIds->count(),
            ];
        });

        return view('livewire.projects.project-financial', compact(
            'project', 'laborRows', 'totalLabor', 'attendanceByEmployee', 'totalLaborReal',
            'materialRows', 'totalMaterials', 'expenseRows', 'totalExpenses',
            'realLaborCost', 'totalCost', 'revenue', 'profit', 'marginPct',
            'comparisonRows', 'budgetTotal', 'activeVersion', 'categoryLabels',
            'physPct', 'financPct', 'progressGap',
            'hoursAssigned', 'hoursReal', 'efficiency', 'milestoneRows',
        ));
    }
}
