<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseRequisition;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class RequisitionIndex extends Component
{
    use WithPagination;

    public string $search          = '';
    public string $filterStatus    = '';
    public string $filterPriority  = '';
    public string $filterType      = '';

    public function updatingSearch(): void          { $this->resetPage(); }
    public function updatingFilterStatus(): void    { $this->resetPage(); }
    public function updatingFilterPriority(): void  { $this->resetPage(); }
    public function updatingFilterType(): void      { $this->resetPage(); }

    public function render()
    {
        $user            = auth()->user();
        $isComprador     = $user->hasRole(['comprador', 'admin', 'gerente', 'super-admin']);

        $requisitions = PurchaseRequisition::query()
            ->when(!$isComprador, fn($q) => $q->where('requested_by', $user->id))
            ->when($this->search, fn($q) => $q
                ->where('folio', 'like', "%{$this->search}%")
                ->orWhere('justification', 'like', "%{$this->search}%")
                ->orWhere('project_name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterType,     fn($q) => $q->where('requisition_type', $this->filterType))
            ->with(['requestedBy', 'branch', 'finalQuotation', 'preliminaryQuotation'])
            ->withCount('items')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.purchases.requisition-index', [
            'requisitions' => $requisitions,
            'isComprador'  => $isComprador,
        ]);
    }
}
