<?php

namespace App\Livewire\Finance;

use App\Models\BankReconciliation;
use App\Models\FinanceAccount;
use App\Models\FinancePeriodClose;
use App\Models\FinanceTransaction;
use App\Models\PurchaseInvoice;
use App\Models\SaleInvoice;
use App\Models\ScheduledPayment;
use App\Models\Tender;
use App\Models\WorkLibranza;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class FinanceDashboard extends Component
{
    public function render()
    {
        $companyId  = auth()->user()->company_id;
        $now        = now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();

        // ── Cuentas activas ───────────────────────────────────────────────
        $accounts = FinanceAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'name', 'currency', 'current_balance', 'type']);

        $accountIds   = $accounts->pluck('id');
        $totalBalance = $accounts->sum('current_balance');

        // ── CxC (facturas de venta pendientes) ────────────────────────────
        $cxcPending = SaleInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->sum(DB::raw('total - paid_amount'));

        $cxcOverdue = SaleInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->where('due_at', '<', $now->toDateString())
            ->sum(DB::raw('total - paid_amount'));

        // ── CxP (facturas de proveedor pendientes) ────────────────────────
        $cxpPending = PurchaseInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->sum(DB::raw('total - paid_amount'));

        $cxpOverdue = PurchaseInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->where('due_at', '<', $now->toDateString())
            ->sum(DB::raw('total - paid_amount'));

        // ── Resultado del mes (FinanceTransaction filtra por account_ids) ──
        $monthIncome = FinanceTransaction::whereIn('account_id', $accountIds)
            ->where('type', 'ingreso')
            ->where('status', 'confirmado')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthExpense = FinanceTransaction::whereIn('account_id', $accountIds)
            ->where('type', 'egreso')
            ->where('status', 'confirmado')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthResult = (float) $monthIncome - (float) $monthExpense;

        // ── Alertas ───────────────────────────────────────────────────────
        $overdueInvoices = SaleInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->where('due_at', '<', $now->toDateString())
            ->count();

        $overduePayables = PurchaseInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->where('due_at', '<', $now->toDateString())
            ->count();

        $scheduledToday = ScheduledPayment::where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('scheduled_date', $now->toDateString())
            ->count();

        $scheduledWeek = ScheduledPayment::where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereBetween('scheduled_date', [
                $now->toDateString(),
                $now->copy()->addDays(7)->toDateString(),
            ])
            ->count();

        $pendingReconciliations = BankReconciliation::where('company_id', $companyId)
            ->whereIn('status', ['draft', 'reviewed'])
            ->count();

        $currentPeriod = FinancePeriodClose::where('company_id', $companyId)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->first();

        $periodClosed = $currentPeriod?->status === 'closed';

        // ── Gráfica ingresos vs egresos (últimos 6 meses) ─────────────────
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m   = $now->copy()->subMonths($i);
            $frm = $m->copy()->startOfMonth()->toDateString();
            $to  = $m->copy()->endOfMonth()->toDateString();

            $inc = FinanceTransaction::whereIn('account_id', $accountIds)
                ->where('type', 'ingreso')->where('status', 'confirmado')
                ->whereBetween('transaction_date', [$frm, $to])
                ->sum('amount');

            $exp = FinanceTransaction::whereIn('account_id', $accountIds)
                ->where('type', 'egreso')->where('status', 'confirmado')
                ->whereBetween('transaction_date', [$frm, $to])
                ->sum('amount');

            $chartData[] = [
                'label'   => ucfirst($m->isoFormat('MMM YY')),
                'income'  => (float) $inc,
                'expense' => (float) $exp,
            ];
        }

        // ── Proyección flujo próximos 30 días ─────────────────────────────
        $projectionDays = [];
        $runningBalance = (float) $totalBalance;
        for ($d = 0; $d < 30; $d++) {
            $day = $now->copy()->addDays($d)->toDateString();

            $outflow = (float) ScheduledPayment::where('company_id', $companyId)
                ->where('status', 'pending')
                ->where('scheduled_date', $day)
                ->sum('amount');

            $inflow = (float) SaleInvoice::where('company_id', $companyId)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->whereRaw('total - paid_amount > 0.005')
                ->whereDate('due_at', $day)
                ->sum(DB::raw('total - paid_amount'));

            $runningBalance += ($inflow - $outflow);

            if ($d % 3 === 0) {
                $projectionDays[] = [
                    'label'   => Carbon::parse($day)->format('d/m'),
                    'balance' => round($runningBalance, 2),
                    'inflow'  => $inflow,
                    'outflow' => $outflow,
                ];
            }
        }

        // ── Licitaciones y Obra ───────────────────────────────────────────
        $libranzasPendientes = WorkLibranza::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->whereIn('status', ['enviada', 'aprobada'])
            ->sum('amount');

        $libranzasPagadasMes = WorkLibranza::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'pagada')
            ->whereBetween('updated_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $tenderesAdjudicados = Tender::where('company_id', $companyId)
            ->where('status', 'adjudicada')
            ->sum('awarded_amount');

        $libranzasPendientesList = WorkLibranza::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->whereIn('status', ['enviada', 'aprobada'])
            ->with(['project:id,name', 'tender:id,folio'])
            ->orderBy('period_end')
            ->limit(5)
            ->get();

        // ── Top 5 deudores (CxC vencida) ──────────────────────────────────
        $topDebtors = SaleInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->select('customer_id', DB::raw('SUM(total - paid_amount) as balance'))
            ->groupBy('customer_id')
            ->orderByDesc('balance')
            ->limit(5)
            ->with('customer:id,name')
            ->get();

        // ── Top 5 acreedores (CxP vencida) ────────────────────────────────
        $topCreditors = PurchaseInvoice::where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->select('supplier_id', DB::raw('SUM(total - paid_amount) as balance'))
            ->groupBy('supplier_id')
            ->orderByDesc('balance')
            ->limit(5)
            ->with('supplier:id,name')
            ->get();

        // ── Próximos pagos programados ─────────────────────────────────────
        $upcomingPayments = ScheduledPayment::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('scheduled_date')
            ->limit(8)
            ->with('supplier:id,name')
            ->get();

        return view('livewire.finance.finance-dashboard', compact(
            'accounts', 'totalBalance',
            'cxcPending', 'cxcOverdue',
            'cxpPending', 'cxpOverdue',
            'monthIncome', 'monthExpense', 'monthResult',
            'overdueInvoices', 'overduePayables',
            'scheduledToday', 'scheduledWeek',
            'pendingReconciliations', 'periodClosed',
            'chartData', 'projectionDays',
            'topDebtors', 'topCreditors',
            'upcomingPayments',
            'libranzasPendientes', 'libranzasPagadasMes',
            'tenderesAdjudicados', 'libranzasPendientesList'
        ));
    }
}
