<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\HrEmployee;
use App\Models\Project;
use App\Models\SaleInvoice;
use App\Models\SalesOpportunity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ExecutiveDashboard extends Component
{
    public string $period = 'month'; // month | quarter | year

    public function render()
    {
        $companyId = auth()->user()->company_id;
        [$from, $to, $prevFrom, $prevTo] = $this->periodDates();

        // ── Ventas ────────────────────────────────────────────────────────
        $revenueNow  = SaleInvoice::where('company_id', $companyId)
            ->whereIn('status', ['stamped', 'paid'])
            ->whereBetween('issued_at', [$from, $to])
            ->sum('total');

        $revenuePrev = SaleInvoice::where('company_id', $companyId)
            ->whereIn('status', ['stamped', 'paid'])
            ->whereBetween('issued_at', [$prevFrom, $prevTo])
            ->sum('total');

        $revenueGrowth = $revenuePrev > 0
            ? round(($revenueNow - $revenuePrev) / $revenuePrev * 100, 1)
            : null;

        // ── Cobranza vencida ──────────────────────────────────────────────
        $overdueAmount = SaleInvoice::where('company_id', $companyId)
            ->whereIn('status', ['stamped'])
            ->where('due_at', '<', now())
            ->selectRaw('SUM(total - COALESCE(paid_amount,0)) as total')
            ->value('total') ?? 0;

        $overdueCount = SaleInvoice::where('company_id', $companyId)
            ->whereIn('status', ['stamped'])
            ->where('due_at', '<', now())
            ->count();

        // ── Compras pendientes ────────────────────────────────────────────
        $pendingPayables = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->whereIn('status', ['approved', 'partial'])
            ->selectRaw('SUM(total - COALESCE(paid_amount,0)) as total')
            ->value('total') ?? 0;

        // ── RRHH ──────────────────────────────────────────────────────────
        $headcount = HrEmployee::where('company_id', $companyId)
            ->where('status', 'active')->count();

        $monthlySalaryCost = HrEmployee::where('company_id', $companyId)
            ->where('status', 'active')
            ->sum('base_salary');

        // ── Proyectos ─────────────────────────────────────────────────────
        $branchIds = Branch::where('company_id', $companyId)->pluck('id');
        $activeProjects = Project::whereIn('branch_id', $branchIds)
            ->where('status', 'activo')->count();

        $projectsOverBudget = Project::whereIn('branch_id', $branchIds)
            ->whereColumn('cost_actual', '>', 'budget')
            ->where('budget', '>', 0)
            ->count();

        // ── Pipeline CRM ──────────────────────────────────────────────────
        $pipelineValue = SalesOpportunity::where('company_id', $companyId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->sum('estimated_value');

        $pipelineWeighted = SalesOpportunity::where('company_id', $companyId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->selectRaw('SUM(estimated_value * probability / 100) as total')
            ->value('total') ?? 0;

        $wonThisPeriod = SalesOpportunity::where('company_id', $companyId)
            ->where('stage', 'won')
            ->whereBetween('won_at', [$from, $to])
            ->sum('estimated_value');

        // ── Tendencia mensual ingresos (6 meses) ──────────────────────────
        $revenueTrend = collect(range(5, 0))->map(function ($i) use ($companyId) {
            $d = now()->subMonths($i);
            $total = SaleInvoice::where('company_id', $companyId)
                ->whereIn('status', ['stamped', 'paid'])
                ->whereYear('issued_at', $d->year)
                ->whereMonth('issued_at', $d->month)
                ->sum('total');
            return ['label' => $d->translatedFormat('M y'), 'value' => (float) $total];
        });

        $maxRevenue = $revenueTrend->max('value') ?: 1;

        // ── Top 5 clientes ────────────────────────────────────────────────
        $topCustomers = DB::table('sale_invoices')
            ->join('customers', 'sale_invoices.customer_id', '=', 'customers.id')
            ->where('sale_invoices.company_id', $companyId)
            ->whereIn('sale_invoices.status', ['stamped', 'paid'])
            ->whereBetween('sale_invoices.issued_at', [$from, $to])
            ->groupBy('customers.id', 'customers.name')
            ->selectRaw('customers.name, SUM(sale_invoices.total) as total, COUNT(*) as invoices')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $maxCustomer = $topCustomers->max('total') ?: 1;

        // ── Alertas ───────────────────────────────────────────────────────
        $alerts = collect();
        if ($overdueAmount > 0) {
            $alerts->push(['type' => 'red', 'msg' => "$overdueCount factura(s) vencida(s) por cobrar — $" . number_format($overdueAmount, 0)]);
        }
        if ($projectsOverBudget > 0) {
            $alerts->push(['type' => 'amber', 'msg' => "$projectsOverBudget proyecto(s) excedieron el presupuesto"]);
        }
        if ($revenueGrowth !== null && $revenueGrowth < -10) {
            $alerts->push(['type' => 'amber', 'msg' => "Ingresos cayeron " . abs($revenueGrowth) . "% vs período anterior"]);
        }
        if ($pipelineValue > 0 && $pipelineWeighted < $pipelineValue * 0.3) {
            $alerts->push(['type' => 'slate', 'msg' => "Pipeline con baja probabilidad ponderada (" . round($pipelineWeighted / $pipelineValue * 100) . "% del valor bruto)"]);
        }

        return view('livewire.reports.executive-dashboard', compact(
            'revenueNow', 'revenuePrev', 'revenueGrowth',
            'overdueAmount', 'overdueCount', 'pendingPayables',
            'headcount', 'monthlySalaryCost',
            'activeProjects', 'projectsOverBudget',
            'pipelineValue', 'pipelineWeighted', 'wonThisPeriod',
            'revenueTrend', 'maxRevenue', 'topCustomers', 'maxCustomer',
            'alerts',
        ));
    }

    private function periodDates(): array
    {
        return match ($this->period) {
            'quarter' => [
                now()->startOfQuarter(),
                now()->endOfQuarter(),
                now()->subQuarter()->startOfQuarter(),
                now()->subQuarter()->endOfQuarter(),
            ],
            'year' => [
                now()->startOfYear(),
                now()->endOfYear(),
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear(),
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ],
        };
    }
}
