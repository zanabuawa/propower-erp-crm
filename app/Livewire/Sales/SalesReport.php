<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class SalesReport extends Component
{
    use WithPagination;

    public string $reportType = 'invoices'; // invoices | orders | quotations | products
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $customerId = '';
    public string $vendorId   = '';
    public string $status     = '';
    public string $sortBy     = 'date';
    public string $sortDir    = 'desc';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->endOfMonth()->toDateString();
    }

    public function updatedReportType(): void  { $this->resetPage(); }
    public function updatedCustomerId(): void  { $this->resetPage(); }
    public function updatedVendorId(): void    { $this->resetPage(); }
    public function updatedStatus(): void      { $this->resetPage(); }
    public function updatedDateFrom(): void    { $this->resetPage(); }
    public function updatedDateTo(): void      { $this->resetPage(); }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
        $this->resetPage();
    }

    public function setThisMonth(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function setThisQuarter(): void
    {
        $this->dateFrom = now()->startOfQuarter()->toDateString();
        $this->dateTo   = now()->endOfQuarter()->toDateString();
        $this->resetPage();
    }

    public function setThisYear(): void
    {
        $this->dateFrom = now()->startOfYear()->toDateString();
        $this->dateTo   = now()->endOfYear()->toDateString();
        $this->resetPage();
    }

    private function cid(): int
    {
        return auth()->user()->company_id;
    }

    // Rows are resolved in render() — NOT as #[Computed] — to ensure pagination works correctly.
    private function resolveRows(string $from, string $to)
    {
        return match ($this->reportType) {
            'invoices'   => $this->invoiceRows($from, $to),
            'orders'     => $this->orderRows($from, $to),
            'quotations' => $this->quotationRows($from, $to),
            'products'   => $this->productRows($from, $to),
            default      => collect(),
        };
    }

    private function invoiceRows(string $from, string $to)
    {
        $sortMap = [
            'date'     => 'sale_invoices.issued_at',
            'folio'    => 'sale_invoices.folio',
            'customer' => 'customers.name',
            'total'    => 'sale_invoices.total',
            'status'   => 'sale_invoices.status',
        ];
        $col = $sortMap[$this->sortBy] ?? 'sale_invoices.issued_at';

        return SaleInvoice::where('sale_invoices.company_id', $this->cid())
            ->join('customers', 'customers.id', '=', 'sale_invoices.customer_id')
            ->leftJoin('users', 'users.id', '=', 'sale_invoices.created_by')
            ->whereBetween('sale_invoices.issued_at', [$from, $to])
            ->when($this->customerId, fn($q) => $q->where('sale_invoices.customer_id', $this->customerId))
            ->when($this->vendorId,   fn($q) => $q->where('sale_invoices.created_by', $this->vendorId))
            ->when($this->status,     fn($q) => $q->where('sale_invoices.status', $this->status))
            ->select('sale_invoices.*', 'customers.name as customer_name', 'users.name as vendor_name')
            ->orderBy($col, $this->sortDir)
            ->paginate(25);
    }

    private function orderRows(string $from, string $to)
    {
        $sortMap = [
            'date'     => 'sale_orders.created_at',
            'folio'    => 'sale_orders.folio',
            'customer' => 'customers.name',
            'total'    => 'sale_orders.total',
            'status'   => 'sale_orders.status',
        ];
        $col = $sortMap[$this->sortBy] ?? 'sale_orders.created_at';

        return SaleOrder::where('sale_orders.company_id', $this->cid())
            ->join('customers', 'customers.id', '=', 'sale_orders.customer_id')
            ->leftJoin('users', 'users.id', '=', 'sale_orders.created_by')
            ->whereBetween('sale_orders.created_at', [$from, $to])
            ->when($this->customerId, fn($q) => $q->where('sale_orders.customer_id', $this->customerId))
            ->when($this->vendorId,   fn($q) => $q->where('sale_orders.created_by', $this->vendorId))
            ->when($this->status,     fn($q) => $q->where('sale_orders.status', $this->status))
            ->select('sale_orders.*', 'customers.name as customer_name', 'users.name as vendor_name')
            ->orderBy($col, $this->sortDir)
            ->paginate(25);
    }

    private function quotationRows(string $from, string $to)
    {
        $sortMap = [
            'date'     => 'sale_quotations.created_at',
            'folio'    => 'sale_quotations.folio',
            'customer' => 'customers.name',
            'total'    => 'sale_quotations.total',
            'status'   => 'sale_quotations.status',
        ];
        $col = $sortMap[$this->sortBy] ?? 'sale_quotations.created_at';

        return SaleQuotation::where('sale_quotations.company_id', $this->cid())
            ->join('customers', 'customers.id', '=', 'sale_quotations.customer_id')
            ->leftJoin('users', 'users.id', '=', 'sale_quotations.created_by')
            ->whereBetween('sale_quotations.created_at', [$from, $to])
            ->when($this->customerId, fn($q) => $q->where('sale_quotations.customer_id', $this->customerId))
            ->when($this->vendorId,   fn($q) => $q->where('sale_quotations.created_by', $this->vendorId))
            ->when($this->status,     fn($q) => $q->where('sale_quotations.status', $this->status))
            ->select('sale_quotations.*', 'customers.name as customer_name', 'users.name as vendor_name')
            ->orderBy($col, $this->sortDir)
            ->paginate(25);
    }

    private function productRows(string $from, string $to)
    {
        $sortMap = [
            'total' => 'total_revenue',
            'qty'   => 'total_qty',
        ];
        $col = $sortMap[$this->sortBy] ?? 'total_revenue';

        return SaleInvoiceItem::join('sale_invoices', 'sale_invoices.id', '=', 'sale_invoice_items.sale_invoice_id')
            ->where('sale_invoices.company_id', $this->cid())
            ->whereNotIn('sale_invoices.status', ['cancelled'])
            ->whereBetween('sale_invoices.issued_at', [$from, $to])
            ->when($this->customerId, fn($q) => $q->where('sale_invoices.customer_id', $this->customerId))
            ->when($this->vendorId,   fn($q) => $q->where('sale_invoices.created_by', $this->vendorId))
            ->selectRaw('
                sale_invoice_items.product_id,
                sale_invoice_items.description,
                coalesce(sum(sale_invoice_items.quantity), 0) as total_qty,
                coalesce(sum(sale_invoice_items.subtotal), 0) as total_revenue,
                count(distinct sale_invoices.customer_id) as customer_count,
                count(distinct sale_invoices.id) as invoice_count
            ')
            ->groupBy('sale_invoice_items.product_id', 'sale_invoice_items.description')
            ->orderBy($col, $this->sortDir)
            ->paginate(25);
    }

    #[Computed]
    public function totals(): array
    {
        $from = $this->dateFrom . ' 00:00:00';
        $to   = $this->dateTo   . ' 23:59:59';

        if ($this->reportType === 'products') {
            $sub = SaleInvoiceItem::join('sale_invoices', 'sale_invoices.id', '=', 'sale_invoice_items.sale_invoice_id')
                ->where('sale_invoices.company_id', $this->cid())
                ->whereNotIn('sale_invoices.status', ['cancelled'])
                ->whereBetween('sale_invoices.issued_at', [$from, $to])
                ->when($this->customerId, fn($q) => $q->where('sale_invoices.customer_id', $this->customerId))
                ->when($this->vendorId,   fn($q) => $q->where('sale_invoices.created_by', $this->vendorId))
                ->selectRaw('coalesce(sum(sale_invoice_items.quantity),0) as total_qty, coalesce(sum(sale_invoice_items.subtotal),0) as total_revenue')
                ->first();
            return ['total_revenue' => (float)$sub->total_revenue, 'total_qty' => (float)$sub->total_qty];
        }

        $query = match($this->reportType) {
            'invoices' => SaleInvoice::where('company_id', $this->cid())
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween('issued_at', [$from, $to])
                ->when($this->status,     fn($q) => $q->where('status', $this->status))
                ->when($this->customerId, fn($q) => $q->where('customer_id', $this->customerId))
                ->when($this->vendorId,   fn($q) => $q->where('created_by', $this->vendorId))
                ->selectRaw('count(*) as qty, coalesce(sum(total),0) as total, coalesce(sum(discount_amount),0) as discount, coalesce(sum(tax),0) as tax'),
            'orders' => SaleOrder::where('company_id', $this->cid())
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween('created_at', [$from, $to])
                ->when($this->status,     fn($q) => $q->where('status', $this->status))
                ->when($this->customerId, fn($q) => $q->where('customer_id', $this->customerId))
                ->when($this->vendorId,   fn($q) => $q->where('created_by', $this->vendorId))
                ->selectRaw('count(*) as qty, coalesce(sum(total),0) as total, coalesce(sum(discount_amount),0) as discount, coalesce(sum(tax),0) as tax'),
            'quotations' => SaleQuotation::where('company_id', $this->cid())
                ->whereBetween('created_at', [$from, $to])
                ->when($this->status,     fn($q) => $q->where('status', $this->status))
                ->when($this->customerId, fn($q) => $q->where('customer_id', $this->customerId))
                ->when($this->vendorId,   fn($q) => $q->where('created_by', $this->vendorId))
                ->selectRaw('count(*) as qty, coalesce(sum(total),0) as total, coalesce(sum(discount_amount),0) as discount, coalesce(sum(tax),0) as tax'),
            default => null,
        };

        if (!$query) return [];

        $agg = $query->first();
        return [
            'qty'      => (int)  $agg->qty,
            'total'    => (float)$agg->total,
            'discount' => (float)$agg->discount,
            'tax'      => (float)$agg->tax,
        ];
    }

    public function render()
    {
        $from = $this->dateFrom . ' 00:00:00';
        $to   = $this->dateTo   . ' 23:59:59';

        $rows = $this->resolveRows($from, $to);

        $customers = Customer::where('company_id', $this->cid())->orderBy('name')->get(['id','name']);
        $vendors   = User::where('company_id', $this->cid())->orderBy('name')->get(['id','name']);

        $statusOptions = match($this->reportType) {
            'invoices'   => SaleInvoice::STATUS,
            'orders'     => SaleOrder::STATUS,
            'quotations' => SaleQuotation::STATUS,
            default      => [],
        };

        return view('livewire.sales.sales-report', compact('customers', 'vendors', 'statusOptions', 'rows'));
    }
}
