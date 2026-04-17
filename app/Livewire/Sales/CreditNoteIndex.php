<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\SaleCreditNote;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CreditNoteIndex extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterStatus  = '';
    public ?int   $filterCustomer = null;

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterStatus(): void   { $this->resetPage(); }
    public function updatingFilterCustomer(): void { $this->resetPage(); }

    public function render()
    {
        $notes = SaleCreditNote::with(['customer', 'invoice', 'createdBy'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('reason', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) =>
                        $c->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus,   fn($q) => $q->where('status',      $this->filterStatus))
            ->when($this->filterCustomer, fn($q) => $q->where('customer_id', $this->filterCustomer))
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.sales.credit-note-index', compact('notes', 'customers'));
    }
}
