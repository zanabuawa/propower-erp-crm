<?php

namespace App\Livewire\Purchases;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchasesAnalytics extends Component
{
    public string $filterFrom = '';
    public string $filterTo   = '';
    public string $period     = 'month';

    public function mount(): void
    {
        $this->filterFrom = now()->startOfYear()->format('Y-m-d');
        $this->filterTo   = now()->format('Y-m-d');
    }

    public function updatedPeriod(): void
    {
        [$from, $to] = match ($this->period) {
            'quarter' => [now()->startOfQuarter()->format('Y-m-d'), now()->endOfQuarter()->format('Y-m-d')],
            'year'    => [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default   => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
        };
        $this->filterFrom = $from;
        $this->filterTo   = $to;
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $from = $this->filterFrom;
        $to   = $this->filterTo . ' 23:59:59';

        // ── KPIs ──────────────────────────────────────────────────────────
        $ordersBase = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from, $to]);

        $totalSpend  = (clone $ordersBase)->sum('total');
        $orderCount  = (clone $ordersBase)->count();
        $avgOrder    = $orderCount > 0 ? $totalSpend / $orderCount : 0;
        $pendingPay  = (clone $ordersBase)->whereIn('status', ['approved', 'partial'])
            ->selectRaw('SUM(total - COALESCE(paid_amount,0)) as t')->value('t') ?? 0;

        // vs período anterior
        $days = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $prevFrom = Carbon::parse($from)->subDays($days)->format('Y-m-d');
        $prevTo   = Carbon::parse($from)->subDay()->format('Y-m-d');
        $prevSpend = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$prevFrom, $prevTo . ' 23:59:59'])
            ->sum('total');
        $spendGrowth = $prevSpend > 0
            ? round(($totalSpend - $prevSpend) / $prevSpend * 100, 1)
            : null;

        // ── Por proveedor ─────────────────────────────────────────────────
        $bySupplier = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->where('purchase_orders.company_id', $companyId)
            ->whereNotIn('purchase_orders.status', ['cancelled'])
            ->whereBetween('purchase_orders.created_at', [$from, $to])
            ->groupBy('suppliers.id', 'suppliers.name')
            ->selectRaw('suppliers.id, suppliers.name, COUNT(*) as orders, SUM(purchase_orders.total) as total, AVG(purchase_orders.total) as avg_order')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $maxSupplier = $bySupplier->max('total') ?: 1;

        // ── Por categoría (vía items → productos → categorías) ────────────
        $byCategory = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('purchase_orders.company_id', $companyId)
            ->whereNotIn('purchase_orders.status', ['cancelled'])
            ->whereBetween('purchase_orders.created_at', [$from, $to])
            ->groupBy('product_categories.id', 'product_categories.name')
            ->selectRaw('product_categories.name, SUM(purchase_order_items.subtotal) as total, COUNT(DISTINCT purchase_orders.id) as orders')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $maxCategory = $byCategory->max('total') ?: 1;

        // ── Evaluación de proveedores ─────────────────────────────────────
        $supplierScores = DB::table('supplier_evaluations')
            ->join('suppliers', 'supplier_evaluations.supplier_id', '=', 'suppliers.id')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->selectRaw('
                suppliers.id,
                suppliers.name,
                COUNT(*) as evaluations,
                ROUND(AVG(score_price),1) as price,
                ROUND(AVG(score_quality),1) as quality,
                ROUND(AVG(score_delivery),1) as delivery,
                ROUND(AVG(score_compliance),1) as compliance,
                ROUND(AVG(score_overall),1) as overall
            ')
            ->orderByDesc('overall')
            ->limit(8)
            ->get();

        // ── Tendencia mensual de gasto (12 meses) ─────────────────────────
        $spendTrend = collect(range(11, 0))->map(function ($i) use ($companyId) {
            $d = now()->subMonths($i);
            $total = DB::table('purchase_orders')
                ->where('company_id', $companyId)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->sum('total');
            return ['label' => $d->translatedFormat('M y'), 'value' => (float) $total];
        });

        $maxTrend = $spendTrend->max('value') ?: 1;

        // ── Órdenes recientes pendientes ──────────────────────────────────
        $pendingOrders = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->where('purchase_orders.company_id', $companyId)
            ->whereIn('purchase_orders.status', ['approved', 'partial'])
            ->orderByDesc('purchase_orders.created_at')
            ->select('purchase_orders.*', 'suppliers.name as supplier_name')
            ->limit(8)
            ->get();

        return view('livewire.purchases.purchases-analytics', compact(
            'totalSpend', 'orderCount', 'avgOrder', 'pendingPay', 'spendGrowth',
            'bySupplier', 'maxSupplier', 'byCategory', 'maxCategory',
            'supplierScores', 'spendTrend', 'maxTrend', 'pendingOrders',
        ));
    }
}
