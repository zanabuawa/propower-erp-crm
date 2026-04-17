<?php

namespace App\Livewire\Finance;

use App\Models\Customer;
use App\Models\SaleInvoice;
use App\Models\SalePayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CollectionsDashboard extends Component
{
    public string $period = 'month'; // month | quarter | year | custom
    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->endOfMonth()->toDateString();
    }

    public function updatedPeriod(string $value): void
    {
        $this->dateFrom = match ($value) {
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => $this->dateFrom,
        };
        $this->dateTo = match ($value) {
            'month'   => now()->endOfMonth()->toDateString(),
            'quarter' => now()->endOfQuarter()->toDateString(),
            'year'    => now()->endOfYear()->toDateString(),
            default   => $this->dateTo,
        };
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    private function pendingInvoicesQuery()
    {
        return SaleInvoice::where('company_id', $this->companyId())
            ->whereIn('status', ['stamped', 'draft'])
            ->whereRaw('total - paid_amount > 0.005');
    }

    // ── KPIs ─────────────────────────────────────────────────────────────

    /** Total pendiente de cobro (todas las facturas con saldo > 0) */
    private function totalReceivable(): float
    {
        return (float) $this->pendingInvoicesQuery()
            ->selectRaw('SUM(total - paid_amount) as balance')
            ->value('balance') ?? 0;
    }

    /** Monto vencido (due_at < hoy) */
    private function overdueAmount(): float
    {
        return (float) $this->pendingInvoicesQuery()
            ->where('due_at', '<', now()->startOfDay())
            ->selectRaw('SUM(total - paid_amount) as balance')
            ->value('balance') ?? 0;
    }

    /** Cobrado en el período */
    private function collectedInPeriod(): float
    {
        return (float) SalePayment::where('company_id', $this->companyId())
            ->where('status', 'applied')
            ->whereBetween('paid_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->sum('amount');
    }

    /** Facturado en el período */
    private function invoicedInPeriod(): float
    {
        return (float) SaleInvoice::where('company_id', $this->companyId())
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('issued_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->sum('total');
    }

    /**
     * DSO = (Saldo total por cobrar / Ventas últimos 90 días) × 90
     * Indica el promedio de días que tarda la empresa en cobrar.
     */
    private function dso(): ?float
    {
        $sales90 = (float) SaleInvoice::where('company_id', $this->companyId())
            ->whereNotIn('status', ['cancelled'])
            ->where('issued_at', '>=', now()->subDays(90))
            ->sum('total');

        if ($sales90 <= 0) return null;

        return round($this->totalReceivable() / $sales90 * 90, 1);
    }

    /** Distribución de antigüedad (buckets) */
    private function agingBuckets(): array
    {
        $today    = now()->startOfDay();
        $invoices = $this->pendingInvoicesQuery()
            ->select(['due_at', DB::raw('total - paid_amount as balance')])
            ->get();

        $buckets = [
            'current' => ['label' => 'Vigente',      'amount' => 0, 'color' => 'bg-green-500'],
            '1-30'    => ['label' => '1-30 días',    'amount' => 0, 'color' => 'bg-yellow-400'],
            '31-60'   => ['label' => '31-60 días',   'amount' => 0, 'color' => 'bg-orange-400'],
            '61-90'   => ['label' => '61-90 días',   'amount' => 0, 'color' => 'bg-red-400'],
            '90+'     => ['label' => '+90 días',      'amount' => 0, 'color' => 'bg-red-700'],
        ];

        foreach ($invoices as $inv) {
            $days   = $inv->due_at
                ? (int) $today->diffInDays($inv->due_at, false) * -1
                : 0;
            $bucket = AccountsReceivableAging::bucket($days);
            $buckets[$bucket]['amount'] += (float) $inv->balance;
        }

        return $buckets;
    }

    /** Top 8 deudores por saldo pendiente */
    private function topDebtors(): \Illuminate\Support\Collection
    {
        return SaleInvoice::where('company_id', $this->companyId())
            ->whereIn('status', ['stamped', 'draft'])
            ->whereRaw('total - paid_amount > 0.005')
            ->select('customer_id', DB::raw('SUM(total - paid_amount) as balance'), DB::raw('COUNT(*) as invoice_count'))
            ->groupBy('customer_id')
            ->orderByDesc('balance')
            ->limit(8)
            ->with('customer:id,name')
            ->get();
    }

    /** Cobros recientes (últimos 8) */
    private function recentPayments(): \Illuminate\Support\Collection
    {
        return SalePayment::where('company_id', $this->companyId())
            ->where('status', 'applied')
            ->with(['customer:id,name', 'invoice:id,folio'])
            ->orderByDesc('paid_at')
            ->limit(8)
            ->get();
    }

    /** Cobros por mes (últimos 6 meses) para mini gráfica */
    private function monthlyCollections(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = (float) SalePayment::where('company_id', $this->companyId())
                ->where('status', 'applied')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount');

            $months[] = [
                'label'  => $month->locale('es')->isoFormat('MMM'),
                'amount' => $amount,
            ];
        }
        return $months;
    }

    // ── Render ────────────────────────────────────────────────────────────
    public function render()
    {
        $totalReceivable  = $this->totalReceivable();
        $overdueAmount    = $this->overdueAmount();
        $overduePercent   = $totalReceivable > 0 ? round($overdueAmount / $totalReceivable * 100, 1) : 0;
        $collectedPeriod  = $this->collectedInPeriod();
        $invoicedPeriod   = $this->invoicedInPeriod();
        $collectionRate   = $invoicedPeriod > 0 ? round($collectedPeriod / $invoicedPeriod * 100, 1) : null;
        $dso              = $this->dso();
        $agingBuckets     = $this->agingBuckets();
        $topDebtors       = $this->topDebtors();
        $recentPayments   = $this->recentPayments();
        $monthlyCollections = $this->monthlyCollections();
        $maxMonthly       = collect($monthlyCollections)->max('amount') ?: 1;

        return view('livewire.finance.collections-dashboard', compact(
            'totalReceivable', 'overdueAmount', 'overduePercent',
            'collectedPeriod', 'invoicedPeriod', 'collectionRate',
            'dso', 'agingBuckets', 'topDebtors', 'recentPayments',
            'monthlyCollections', 'maxMonthly'
        ));
    }
}
