<?php

namespace App\Providers;

use App\Models\FinanceCashflow;
use App\Models\FinanceTransaction;
use App\Models\ProjectExpense;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\PriceListItem;
use App\Models\SalePayment;
use App\Observers\FinanceCashflowObserver;
use App\Observers\FinanceTransactionObserver;
use App\Observers\PriceListItemObserver;
use App\Observers\ProjectExpenseObserver;
use App\Observers\ProjectMilestoneObserver;
use App\Observers\ProjectTaskObserver;
use App\Observers\SalePaymentObserver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return LengthAwarePaginator
         */
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
                $page = $page ?: Paginator::resolveCurrentPage($pageName);

                return new LengthAwarePaginator(
                    $this->forPage($page, $perPage),
                    $total ?: $this->count(),
                    $perPage,
                    $page,
                    [
                        'path' => Paginator::resolveCurrentPath(),
                        'pageName' => $pageName,
                    ]
                );
            });
        }

        // super-admin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // ── Pseudo-disparadores entre módulos ─────────────────────────────────
        // Proyectos → recalcula progreso al guardar/eliminar tareas
        ProjectTask::observe(ProjectTaskObserver::class);

        // Proyectos → recalcula cost_actual; aprobado → crea FinanceTransaction
        ProjectExpense::observe(ProjectExpenseObserver::class);

        // Hitos → crea cashflow proyectado; completado → lo marca realizado
        ProjectMilestone::observe(ProjectMilestoneObserver::class);

        // Finanzas → actualiza saldo de cuenta + amount_actual del presupuesto
        FinanceTransaction::observe(FinanceTransactionObserver::class);

        // Flujo de caja realizado → actualiza amount_actual del presupuesto
        FinanceCashflow::observe(FinanceCashflowObserver::class);

        // Pagos de venta cancelados → anular transacción financiera vinculada
        SalePayment::observe(SalePaymentObserver::class);

        // Listas de precios → registra historial de cambios de precio
        PriceListItem::observe(PriceListItemObserver::class);
    }
}
