<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PurchaseInvoiceIndex extends Component
{
    use WithPagination;

    public string $search          = '';
    public string $filterStatus    = '';
    public string $filterMatch     = '';
    public ?int   $filterSupplier  = null;
    public string $filterDateFrom  = '';
    public string $filterDateTo    = '';

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterStatus(): void   { $this->resetPage(); }
    public function updatingFilterMatch(): void    { $this->resetPage(); }
    public function updatingFilterSupplier(): void { $this->resetPage(); }

    public function render()
    {
        $invoices = PurchaseInvoice::with(['supplier', 'order', 'createdBy'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('supplier_invoice_number', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) =>
                        $s->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus,   fn($q) => $q->where('status',       $this->filterStatus))
            ->when($this->filterMatch,    fn($q) => $q->where('match_status', $this->filterMatch))
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id',  $this->filterSupplier))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('issued_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,   fn($q) => $q->whereDate('issued_at', '<=', $this->filterDateTo))
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        // KPIs rápidos
        $base = PurchaseInvoice::where('company_id', auth()->user()->company_id);
        $kpis = [
            'pending'     => (clone $base)->where('status', 'pending')->count(),
            'discrepancy' => (clone $base)->where('match_status', 'discrepancy')->count(),
            'overdue'     => (clone $base)->whereNotIn('status', ['paid', 'cancelled'])
                                ->where('due_at', '<', now())->count(),
            'total_pending' => (float) (clone $base)->whereNotIn('status', ['paid', 'cancelled'])
                                ->selectRaw('SUM(total - paid_amount)')->value('SUM(total - paid_amount)'),
        ];

        return view('livewire.purchases.purchase-invoice-index', compact('invoices', 'suppliers', 'kpis'));
    }
}
