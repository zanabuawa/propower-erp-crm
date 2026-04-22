<?php

namespace App\Livewire\Sales;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CustomerAnalytics extends Component
{
    public string $filterFrom = '';
    public string $filterTo   = '';
    public string $search     = '';

    public function mount(): void
    {
        $this->filterFrom = now()->startOfYear()->format('Y-m-d');
        $this->filterTo   = now()->format('Y-m-d');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $from = $this->filterFrom;
        $to   = $this->filterTo . ' 23:59:59';

        // ── Clientes con facturación en el período ─────────────────────────
        $customers = DB::table('sale_invoices')
            ->join('customers', 'sale_invoices.customer_id', '=', 'customers.id')
            ->where('sale_invoices.company_id', $companyId)
            ->whereIn('sale_invoices.status', ['stamped', 'paid'])
            ->whereBetween('sale_invoices.issued_at', [$from, $to])
            ->when($this->search, fn($q) => $q->where('customers.name', 'like', "%{$this->search}%"))
            ->groupBy('customers.id', 'customers.name')
            ->selectRaw('
                customers.id,
                customers.name,
                COUNT(sale_invoices.id) as invoices,
                SUM(sale_invoices.total) as revenue,
                AVG(sale_invoices.total) as avg_ticket,
                MIN(sale_invoices.issued_at) as first_invoice,
                MAX(sale_invoices.issued_at) as last_invoice,
                SUM(sale_invoices.total - COALESCE(sale_invoices.paid_amount,0)) as pending
            ')
            ->orderByDesc('revenue')
            ->get();

        // ── KPIs globales ─────────────────────────────────────────────────
        $totalRevenue = $customers->sum('revenue');
        $totalCustomers = $customers->count();
        $avgTicket = $customers->avg('avg_ticket') ?? 0;
        $avgFrequency = $customers->avg('invoices') ?? 0;

        // ── Pareto: 20% clientes = X% ingresos ───────────────────────────
        $top20pct = max(1, (int) ceil($totalCustomers * 0.2));
        $top20revenue = $customers->take($top20pct)->sum('revenue');
        $paretoPct = $totalRevenue > 0 ? round($top20revenue / $totalRevenue * 100, 1) : null;

        // ── Segmentación por recencia ─────────────────────────────────────
        $now = now();
        $segments = $customers->map(function ($c) use ($now, $totalRevenue) {
            $last = Carbon::parse($c->last_invoice);
            $first = Carbon::parse($c->first_invoice);
            $daysSince = $last->diffInDays($now);
            $monthsActive = max(1, $first->diffInMonths($now) + 1);
            $ltv = $c->revenue / $monthsActive * 12; // proyección anual

            $segment = match (true) {
                $daysSince <= 30  => 'activo',
                $daysSince <= 90  => 'en_riesgo',
                $daysSince <= 180 => 'inactivo',
                default           => 'perdido',
            };

            return (object) array_merge((array) $c, [
                'days_since'    => $daysSince,
                'months_active' => $monthsActive,
                'ltv_annual'    => round($ltv, 2),
                'revenue_pct'   => $totalRevenue > 0 ? round($c->revenue / $totalRevenue * 100, 1) : 0,
                'segment'       => $segment,
            ]);
        });

        $bySegment = $segments->groupBy('segment')->map(fn($rows, $seg) => [
            'label'   => match ($seg) {
                'activo'    => 'Activo (≤30 días)',
                'en_riesgo' => 'En riesgo (31–90 días)',
                'inactivo'  => 'Inactivo (91–180 días)',
                'perdido'   => 'Perdido (>180 días)',
                default     => $seg,
            },
            'color'   => match ($seg) {
                'activo'    => 'emerald',
                'en_riesgo' => 'amber',
                'inactivo'  => 'orange',
                'perdido'   => 'red',
                default     => 'slate',
            },
            'count'   => $rows->count(),
            'revenue' => $rows->sum('revenue'),
        ])->values();

        // ── Tendencia de ingresos por cliente (top 5) ─────────────────────
        $top5ids = $customers->take(5)->pluck('id')->toArray();
        $top5Trend = collect(range(5, 0))->map(function ($i) use ($companyId, $top5ids, $customers) {
            $d = now()->subMonths($i);
            $row = ['label' => $d->translatedFormat('M y')];
            foreach ($top5ids as $cid) {
                $row['c_' . $cid] = (float) DB::table('sale_invoices')
                    ->where('company_id', $companyId)
                    ->where('customer_id', $cid)
                    ->whereIn('status', ['stamped', 'paid'])
                    ->whereYear('issued_at', $d->year)
                    ->whereMonth('issued_at', $d->month)
                    ->sum('total');
            }
            return $row;
        });

        $top5names = $customers->take(5)->mapWithKeys(fn($c) => ['c_' . $c->id => $c->name]);

        // ── Nuevos clientes por mes ───────────────────────────────────────
        $newCustomersTrend = collect(range(11, 0))->map(function ($i) use ($companyId) {
            $d = now()->subMonths($i);
            $count = DB::table('customers')
                ->where('company_id', $companyId)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
            return ['label' => $d->translatedFormat('M'), 'count' => $count];
        });
        $maxNewCustomers = $newCustomersTrend->max('count') ?: 1;

        $maxRevenueCust = $segments->max('revenue') ?: 1;

        return view('livewire.sales.customer-analytics', compact(
            'segments', 'totalRevenue', 'totalCustomers', 'avgTicket',
            'avgFrequency', 'paretoPct', 'top20pct', 'bySegment',
            'top5Trend', 'top5names', 'newCustomersTrend', 'maxNewCustomers',
            'maxRevenueCust',
        ));
    }
}
