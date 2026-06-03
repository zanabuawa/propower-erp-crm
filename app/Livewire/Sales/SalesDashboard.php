<?php

namespace App\Livewire\Sales;

use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class SalesDashboard extends Component
{
    public string $period   = 'month';
    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->endOfMonth()->toDateString();
    }

    public function updatedPeriod(): void
    {
        match ($this->period) {
            'month'   => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->toDateString(),   now()->endOfMonth()->toDateString()],
            'quarter' => [$this->dateFrom, $this->dateTo] = [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString()],
            'year'    => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->toDateString(),    now()->endOfYear()->toDateString()],
            default   => null,
        };
    }

    private function cid(): int
    {
        return auth()->user()->company_id;
    }

    #[Computed]
    public function kpis(): array
    {
        $cid  = $this->cid();
        $from = $this->dateFrom;
        $to   = $this->dateTo;

        $invoiced = SaleInvoice::where('company_id', $cid)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('issued_at', [$from, $to . ' 23:59:59'])
            ->selectRaw('count(*) as qty, coalesce(sum(total),0) as total, coalesce(sum(paid_amount),0) as paid, coalesce(sum(tax),0) as tax')
            ->first();

        $orders = SaleOrder::where('company_id', $cid)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->selectRaw('count(*) as qty, coalesce(sum(total),0) as total')
            ->first();

        $quotations = SaleQuotation::where('company_id', $cid)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->selectRaw('count(*) as qty')
            ->first();

        $pending   = (float) $invoiced->total - (float) $invoiced->paid;
        $avgTicket = $invoiced->qty > 0 ? (float) $invoiced->total / $invoiced->qty : 0;
        $convRate  = $quotations->qty > 0
            ? round($orders->qty / $quotations->qty * 100, 1)
            : 0;

        // Comparar con período anterior de igual duración
        $days     = max(1, \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to)) + 1);
        $prevFrom = \Carbon\Carbon::parse($from)->subDays($days)->toDateString();
        $prevTo   = \Carbon\Carbon::parse($from)->subDay()->toDateString();

        $prevTotal = SaleInvoice::where('company_id', $cid)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('issued_at', [$prevFrom, $prevTo . ' 23:59:59'])
            ->sum('total');

        $growthPct = $prevTotal > 0
            ? round(((float) $invoiced->total - (float) $prevTotal) / (float) $prevTotal * 100, 1)
            : null;

        return [
            'invoiced_total'  => (float) $invoiced->total,
            'invoiced_qty'    => (int)   $invoiced->qty,
            'invoiced_tax'    => (float) $invoiced->tax,
            'pending_amount'  => $pending,
            'orders_total'    => (float) $orders->total,
            'orders_qty'      => (int)   $orders->qty,
            'quotations_qty'  => (int)   $quotations->qty,
            'avg_ticket'      => $avgTicket,
            'conv_rate'       => $convRate,
            'growth_pct'      => $growthPct,
        ];
    }

    #[Computed]
    public function monthlyTrend(): array
    {
        return SaleInvoice::where('company_id', $this->cid())
            ->whereNotIn('status', ['cancelled'])
            ->where('issued_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(issued_at, '%Y-%m') as month, coalesce(sum(total),0) as total, count(*) as qty")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $r->month)->translatedFormat('M Y'),
                'total' => (float) $r->total,
                'qty'   => (int)   $r->qty,
            ])
            ->toArray();
    }

    #[Computed]
    public function topCustomers(): array
    {
        return SaleInvoice::where('sale_invoices.company_id', $this->cid())
            ->whereNotIn('sale_invoices.status', ['cancelled'])
            ->whereBetween('sale_invoices.issued_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->join('customers', 'customers.id', '=', 'sale_invoices.customer_id')
            ->selectRaw('customers.name, customers.id, count(*) as qty, coalesce(sum(sale_invoices.total),0) as total')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function topProducts(): array
    {
        return SaleInvoiceItem::join('sale_invoices', 'sale_invoices.id', '=', 'sale_invoice_items.sale_invoice_id')
            ->where('sale_invoices.company_id', $this->cid())
            ->whereNotIn('sale_invoices.status', ['cancelled'])
            ->whereBetween('sale_invoices.issued_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw('sale_invoice_items.description, sale_invoice_items.product_id,
                coalesce(sum(sale_invoice_items.quantity),0) as total_qty,
                coalesce(sum(sale_invoice_items.subtotal),0) as total_revenue')
            ->groupBy('sale_invoice_items.product_id', 'sale_invoice_items.description')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function funnel(): array
    {
        $cid  = $this->cid();
        $from = $this->dateFrom;
        $to   = $this->dateTo . ' 23:59:59';

        $quot = SaleQuotation::where('company_id', $cid)->whereBetween('created_at', [$from, $to])->count();
        $ord  = SaleOrder::where('company_id', $cid)->whereBetween('created_at', [$from, $to])->count();
        $inv  = SaleInvoice::where('company_id', $cid)->whereNotIn('status', ['cancelled'])->whereBetween('issued_at', [$from, $to])->count();

        return [
            ['label' => 'Cotizaciones', 'value' => $quot, 'color' => 'bg-blue-500'],
            ['label' => 'Órdenes',      'value' => $ord,  'color' => 'bg-indigo-500'],
            ['label' => 'Facturas',     'value' => $inv,  'color' => 'bg-emerald-500'],
        ];
    }

    #[Computed]
    public function byVendor(): array
    {
        return SaleInvoice::where('sale_invoices.company_id', $this->cid())
            ->whereNotIn('sale_invoices.status', ['cancelled'])
            ->whereBetween('sale_invoices.issued_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->join('users', 'users.id', '=', 'sale_invoices.created_by')
            ->selectRaw('users.name, count(*) as qty, coalesce(sum(sale_invoices.total),0) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.sales.sales-dashboard');
    }
}
