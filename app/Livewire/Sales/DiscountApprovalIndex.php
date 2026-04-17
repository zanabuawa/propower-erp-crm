<?php

namespace App\Livewire\Sales;

use App\Models\DiscountApproval;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DiscountApprovalIndex extends Component
{
    use WithPagination;

    public string $filterStatus = 'pending';
    public ?int   $approvalId   = null;
    public string $approverNotes = '';
    public string $action        = '';

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openModal(int $id, string $action): void
    {
        $this->approvalId    = $id;
        $this->action        = $action;
        $this->approverNotes = '';
    }

    public function closeModal(): void
    {
        $this->approvalId    = null;
        $this->action        = '';
        $this->approverNotes = '';
    }

    public function confirm(): void
    {
        $approval = DiscountApproval::find($this->approvalId);
        if (!$approval) {
            $this->closeModal();
            return;
        }

        if ($this->action === 'approve') {
            $approval->approve(auth()->id(), $this->approverNotes ?: null);
            session()->flash('success', 'Descuento autorizado correctamente.');
        } elseif ($this->action === 'reject') {
            $approval->reject(auth()->id(), $this->approverNotes ?: null);
            session()->flash('success', 'Solicitud rechazada.');
        }

        $this->closeModal();
    }

    public function render()
    {
        $approvals = DiscountApproval::with(['requester', 'approvable'])
            ->where('company_id', auth()->user()->company_id)
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(20);

        $counts = DiscountApproval::where('company_id', auth()->user()->company_id)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.sales.discount-approval-index', compact('approvals', 'counts'));
    }
}
