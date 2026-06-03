<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public ?int $branchId = null;

    public function mount()
    {
        if (!auth()->user()->can('view dashboard')) {
            return $this->redirect(route('hr.portal'), navigate: true);
        }
    }

    public function render()
    {
        $user       = auth()->user();
        $now        = now();
        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfMonth();
        $startPrev  = $now->copy()->subMonth()->startOfMonth();
        $endPrev    = $now->copy()->subMonth()->endOfMonth();

        // Sucursales disponibles para el selector (solo si hay más de una)
        $branches = Branch::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        // ── Ventas ────────────────────────────────────────────────────────────
        $salesThisMonth        = 0;
        $salesPrevMonth        = 0;
        $salesGrowth           = null;
        $activeOrders          = 0;
        $pendingQuotations     = 0;
        $newCustomersThisMonth = 0;
        $salesChart            = collect();
        $recentOrders          = collect();
        $topProducts           = collect();

        if ($user->can('view sales summary')) {
            $ordersBase = SaleOrder::whereNotIn('status', ['cancelled'])
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId));

            $salesThisMonth = (clone $ordersBase)
                ->whereBetween('created_at', [$startMonth, $endMonth])
                ->sum('total');

            $salesPrevMonth = (clone $ordersBase)
                ->whereBetween('created_at', [$startPrev, $endPrev])
                ->sum('total');

            $salesGrowth = $salesPrevMonth > 0
                ? round((($salesThisMonth - $salesPrevMonth) / $salesPrevMonth) * 100, 1)
                : null;

            $activeOrders = (clone $ordersBase)
                ->whereNotIn('status', ['invoiced'])
                ->count();

            $pendingQuotations = SaleQuotation::where('status', 'sent')->count();

            $newCustomersThisMonth = Customer::whereBetween('created_at', [$startMonth, $endMonth])->count();

            $salesChart = collect(range(5, 0))->map(function ($i) use ($now, $ordersBase) {
                $month = $now->copy()->subMonths($i);
                $total = (clone $ordersBase)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total');
                return [
                    'label' => ucfirst($month->translatedFormat('M')),
                    'value' => (float) $total,
                ];
            });

            $recentOrders = SaleOrder::with('customer')
                ->whereNotIn('status', ['cancelled'])
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->latest()
                ->take(5)
                ->get();

            // Top 5 productos más vendidos del mes
            $topProducts = SaleInvoiceItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                ->with('product:id,name,sku')
                ->whereHas('invoice', fn($q) => $q
                    ->whereNotIn('status', ['cancelled'])
                    ->whereBetween('created_at', [$startMonth, $endMonth])
                )
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get();
        }

        // ── Compras ───────────────────────────────────────────────────────────
        $pendingRequisitions = 0;
        $openPurchaseOrders  = 0;
        $pendingReqList      = collect();

        if ($user->can('view purchases summary')) {
            $pendingRequisitions = PurchaseRequisition::whereIn('status', [
                'submitted', 'preliminary_quoted', 'requester_returned',
                'requester_confirmed', 'final_quoted', 'pending_auth',
            ])
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->count();

            $openPurchaseOrders = PurchaseOrder::whereIn('status', [
                'sent', 'waiting_delivery', 'partial_received',
            ])
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->count();

            $pendingReqList = PurchaseRequisition::with('requestedBy')
                ->whereIn('status', ['submitted', 'pending_auth', 'requester_confirmed'])
                ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
                ->latest('updated_at')
                ->take(5)
                ->get();
        }

        // ── Inventario ────────────────────────────────────────────────────────
        $lowStockProducts = 0;

        if ($user->can('view inventory summary')) {
            $lowStockProducts = Product::where('is_active', true)
                ->where('min_stock', '>', 0)
                ->get()
                ->filter(fn($p) => $p->totalStock < $p->min_stock)
                ->count();
        }

        // ── Cobranza ──────────────────────────────────────────────────────────
        $overdueInvoices     = 0;
        $overdueTotal        = 0;
        $pendingInvoicesList = collect();

        if ($user->can('view finance summary')) {
            $overdueInvoices = SaleInvoice::whereIn('status', ['stamped'])
                ->where('due_at', '<', $now)
                ->whereColumn('paid_amount', '<', 'total')
                ->count();

            $overdueTotal = SaleInvoice::whereIn('status', ['stamped'])
                ->where('due_at', '<', $now)
                ->whereColumn('paid_amount', '<', 'total')
                ->sum(DB::raw('total - paid_amount'));

            $pendingInvoicesList = SaleInvoice::with('customer')
                ->whereIn('status', ['stamped'])
                ->whereColumn('paid_amount', '<', 'total')
                ->orderBy('due_at')
                ->take(5)
                ->get();
        }

        return view('livewire.dashboard', compact(
            'branches',
            'salesThisMonth', 'salesGrowth',
            'activeOrders', 'pendingQuotations',
            'newCustomersThisMonth',
            'pendingRequisitions', 'openPurchaseOrders',
            'lowStockProducts',
            'overdueInvoices', 'overdueTotal', 'pendingInvoicesList',
            'salesChart', 'recentOrders', 'pendingReqList',
            'topProducts',
        ));
    }
}
