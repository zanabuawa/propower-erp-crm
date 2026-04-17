<?php

namespace App\Livewire\Finance;

use App\Models\PurchaseInvoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AccountsPayableAging extends Component
{
    public string  $search          = '';
    public string  $filterBucket    = '';
    public ?int    $filterSupplier  = null;
    public int     $perPage         = 25;
    public int     $page            = 1;

    public function updatingSearch(): void         { $this->page = 1; }
    public function updatingFilterBucket(): void   { $this->page = 1; }
    public function updatingFilterSupplier(): void { $this->page = 1; }

    public static function bucket(int $daysOverdue): string
    {
        return match(true) {
            $daysOverdue <= 0  => 'current',
            $daysOverdue <= 30 => '1-30',
            $daysOverdue <= 60 => '31-60',
            $daysOverdue <= 90 => '61-90',
            default            => '90+',
        };
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Todas las facturas pendientes de pago
        $query = PurchaseInvoice::with(['supplier', 'order'])
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('total - paid_amount > 0.005')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
            ->latest('due_at')
            ->get();

        // Calcular días de mora y bucket para cada factura
        $today = now()->startOfDay();
        $rows  = $query->map(function ($inv) use ($today) {
            $daysOverdue = $inv->due_at
                ? (int) $inv->due_at->startOfDay()->diffInDays($today, false)
                : 0;
            return [
                'invoice'     => $inv,
                'balance'     => (float) $inv->total - (float) $inv->paid_amount,
                'days_overdue'=> $daysOverdue,
                'bucket'      => self::bucket($daysOverdue),
            ];
        });

        // Filtrar por bucket si está seleccionado
        if ($this->filterBucket) {
            $rows = $rows->filter(fn($r) => $r['bucket'] === $this->filterBucket)->values();
        }

        // Resumen por bucket
        $summary = [
            'current' => ['count' => 0, 'amount' => 0],
            '1-30'    => ['count' => 0, 'amount' => 0],
            '31-60'   => ['count' => 0, 'amount' => 0],
            '61-90'   => ['count' => 0, 'amount' => 0],
            '90+'     => ['count' => 0, 'amount' => 0],
        ];

        // Siempre calcular el summary sobre todos los rows sin filtro de bucket
        $allRows = $query->map(function ($inv) use ($today) {
            $daysOverdue = $inv->due_at
                ? (int) $inv->due_at->startOfDay()->diffInDays($today, false)
                : 0;
            return [
                'balance' => (float) $inv->total - (float) $inv->paid_amount,
                'bucket'  => self::bucket($daysOverdue),
            ];
        });

        foreach ($allRows as $r) {
            $summary[$r['bucket']]['count']++;
            $summary[$r['bucket']]['amount'] += $r['balance'];
        }

        $totalBalance = collect($summary)->sum('amount');

        // Paginación PHP
        $offset  = ($this->page - 1) * $this->perPage;
        $paged   = new LengthAwarePaginator(
            $rows->slice($offset, $this->perPage)->values(),
            $rows->count(),
            $this->perPage,
            $this->page,
            ['pageName' => 'page']
        );

        // Proveedores para filtro
        $suppliers = \App\Models\Supplier::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('livewire.finance.accounts-payable-aging', compact(
            'paged', 'summary', 'totalBalance', 'suppliers'
        ));
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }
}
