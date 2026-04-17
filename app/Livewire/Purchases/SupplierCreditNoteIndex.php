<?php

namespace App\Livewire\Purchases;

use App\Models\Supplier;
use App\Models\SupplierCreditNote;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SupplierCreditNoteIndex extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $filterStatus   = '';
    public string $filterReason   = '';
    public ?int   $filterSupplier = null;

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterStatus(): void   { $this->resetPage(); }
    public function updatingFilterReason(): void   { $this->resetPage(); }
    public function updatingFilterSupplier(): void { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $notes = SupplierCreditNote::with(['supplier', 'invoice', 'createdBy'])
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('supplier_credit_note_number', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterReason,   fn($q) => $q->where('reason', $this->filterReason))
            ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $base = SupplierCreditNote::where('company_id', $companyId);
        $kpis = [
            'draft'          => (clone $base)->where('status', 'draft')->count(),
            'total_pending'  => (float) (clone $base)->whereIn('status', ['draft', 'partial'])
                                    ->selectRaw('SUM(total - applied_amount)')->value('SUM(total - applied_amount)'),
            'applied_month'  => (float) (clone $base)->where('status', 'applied')
                                    ->whereMonth('applied_at', now()->month)
                                    ->sum('total'),
            'total_count'    => (clone $base)->whereNotIn('status', ['cancelled'])->count(),
        ];

        return view('livewire.purchases.supplier-credit-note-index', compact('notes', 'suppliers', 'kpis'));
    }
}
