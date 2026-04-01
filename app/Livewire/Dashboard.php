<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\SaleInvoice;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $now        = now();
        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfMonth();
        $startPrev  = $now->copy()->subMonth()->startOfMonth();
        $endPrev    = $now->copy()->subMonth()->endOfMonth();

        // ── Ventas ────────────────────────────────────────────────────────────
        $salesThisMonth = SaleOrder::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->sum('total');

        $salesPrevMonth = SaleOrder::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startPrev, $endPrev])
            ->sum('total');

        $salesGrowth = $salesPrevMonth > 0
            ? round((($salesThisMonth - $salesPrevMonth) / $salesPrevMonth) * 100, 1)
            : null;

        $activeOrders      = SaleOrder::whereNotIn('status', ['cancelled', 'invoiced'])->count();
        $pendingQuotations = SaleQuotation::where('status', 'sent')->count();

        // ── Compras ───────────────────────────────────────────────────────────
        $pendingRequisitions = PurchaseRequisition::whereIn('status', [
            'submitted', 'preliminary_quoted', 'requester_returned',
            'requester_confirmed', 'final_quoted', 'pending_auth',
        ])->count();

        $openPurchaseOrders = PurchaseOrder::whereIn('status', [
            'sent', 'waiting_delivery', 'partial_received',
        ])->count();

        // ── Clientes ──────────────────────────────────────────────────────────
        $newCustomersThisMonth = Customer::whereBetween('created_at', [$startMonth, $endMonth])->count();

        // ── Inventario: productos bajo mínimo ─────────────────────────────────
        $lowStockProducts = Product::where('is_active', true)
            ->where('min_stock', '>', 0)
            ->get()
            ->filter(fn($p) => $p->totalStock < $p->min_stock)
            ->count();

        // ── Gráfico ventas últimos 6 meses ────────────────────────────────────
        $salesChart = collect(range(5, 0))->map(function ($i) use ($now) {
            $month = $now->copy()->subMonths($i);
            $total = SaleOrder::whereNotIn('status', ['cancelled'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total');
            return [
                'label' => ucfirst($month->translatedFormat('M')),
                'value' => (float) $total,
            ];
        });

        // ── Últimas órdenes de venta ──────────────────────────────────────────
        $recentOrders = SaleOrder::with('customer')
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->take(5)
            ->get();

        // ── Requisiciones pendientes ──────────────────────────────────────────
        $pendingReqList = PurchaseRequisition::with('requestedBy')
            ->whereIn('status', ['submitted', 'pending_auth', 'requester_confirmed'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('livewire.dashboard', compact(
            'salesThisMonth', 'salesGrowth',
            'activeOrders', 'pendingQuotations',
            'pendingRequisitions', 'openPurchaseOrders',
            'newCustomersThisMonth', 'lowStockProducts',
            'salesChart', 'recentOrders', 'pendingReqList',
        ));
    }
}
