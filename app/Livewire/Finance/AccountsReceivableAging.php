<?php

namespace App\Livewire\Finance;

use App\Models\Customer;
use App\Models\SaleInvoice;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AccountsReceivableAging extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterBucket  = '';   // current|1-30|31-60|61-90|90+
    public ?int   $filterCustomer = null;
    public string $filterDateAt  = '';   // fecha de corte (default: hoy)

    public function mount(): void
    {
        $this->filterDateAt = now()->toDateString();
    }

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingFilterBucket(): void  { $this->resetPage(); }
    public function updatingFilterCustomer(): void { $this->resetPage(); }
    public function updatingFilterDateAt(): void  { $this->resetPage(); }

    // ────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────
    private function cutoffDate(): Carbon
    {
        return $this->filterDateAt
            ? Carbon::parse($this->filterDateAt)->endOfDay()
            : now()->endOfDay();
    }

    /** Devuelve el bucket de antigüedad dado el número de días vencido. */
    public static function bucket(int $daysOverdue): string
    {
        if ($daysOverdue <= 0)  return 'current';
        if ($daysOverdue <= 30) return '1-30';
        if ($daysOverdue <= 60) return '31-60';
        if ($daysOverdue <= 90) return '61-90';
        return '90+';
    }

    // ────────────────────────────────────────────────
    // Base query
    // ────────────────────────────────────────────────
    private function baseQuery()
    {
        $cutoff = $this->cutoffDate();

        return SaleInvoice::with('customer')
            ->where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['stamped', 'draft'])          // pendientes de cobro
            ->whereRaw('total - paid_amount > 0.005')          // saldo real pendiente
            ->where('issued_at', '<=', $cutoff)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) =>
                        $c->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterCustomer, fn($q) =>
                $q->where('customer_id', $this->filterCustomer));
    }

    // ────────────────────────────────────────────────
    // Totales por bucket (para tarjetas de resumen)
    // ────────────────────────────────────────────────
    private function bucketTotals(): array
    {
        $cutoff = $this->cutoffDate();
        $invoices = $this->baseQuery()->get(['total', 'paid_amount', 'due_at']);

        $totals = [
            'current' => 0,
            '1-30'    => 0,
            '31-60'   => 0,
            '61-90'   => 0,
            '90+'     => 0,
        ];

        foreach ($invoices as $inv) {
            $balance = $inv->total - $inv->paid_amount;
            $days    = $inv->due_at
                ? (int) $cutoff->startOfDay()->diffInDays($inv->due_at, false) * -1
                : 0;
            $totals[self::bucket($days)] += $balance;
        }

        return $totals;
    }

    // ────────────────────────────────────────────────
    // Render
    // ────────────────────────────────────────────────
    public function render()
    {
        $cutoff  = $this->cutoffDate();
        $buckets = $this->bucketTotals();
        $total   = array_sum($buckets);

        // Aplicar filtro de bucket
        $query = $this->baseQuery()->orderBy('due_at')->orderBy('customer_id');

        // Post-filtro por bucket (no se puede hacer en SQL de forma limpia sin columna calculada)
        // Traemos todo y filtramos en PHP — para tablas grandes considerar paginate manual
        if ($this->filterBucket) {
            $all = $query->get();
            $filtered = $all->filter(function ($inv) use ($cutoff) {
                $days = $inv->due_at
                    ? (int) $cutoff->copy()->startOfDay()->diffInDays($inv->due_at, false) * -1
                    : 0;
                return self::bucket($days) === $this->filterBucket;
            });

            // Paginación manual
            $page     = $this->getPage();
            $perPage  = 25;
            $invoices = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered->forPage($page, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $invoices = $query->paginate(25);
        }

        $customers = Customer::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.finance.accounts-receivable-aging', compact(
            'invoices', 'buckets', 'total', 'customers', 'cutoff'
        ));
    }
}
