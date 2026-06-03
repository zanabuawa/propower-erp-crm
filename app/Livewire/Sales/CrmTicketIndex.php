<?php

namespace App\Livewire\Sales;

use App\Models\CrmTicket;
use App\Models\Customer;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmTicketIndex extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $status     = '';
    public string $priority   = '';
    public string $type       = '';
    public string $customerId = '';
    public string $assignedTo = '';

    public function updatedSearch():     void { $this->resetPage(); }
    public function updatedStatus():     void { $this->resetPage(); }
    public function updatedPriority():   void { $this->resetPage(); }
    public function updatedType():       void { $this->resetPage(); }
    public function updatedCustomerId(): void { $this->resetPage(); }
    public function updatedAssignedTo(): void { $this->resetPage(); }

    public function render()
    {
        $cid = auth()->user()->company_id;

        $tickets = CrmTicket::where('company_id', $cid)
            ->with(['customer', 'assignedTo', 'createdBy'])
            ->withCount('messages')
            ->when($this->search,     fn($q) => $q->where(fn($q) =>
                $q->where('folio',   'like', "%{$this->search}%")
                  ->orWhere('subject', 'like', "%{$this->search}%")
            ))
            ->when($this->status,     fn($q) => $q->where('status',      $this->status))
            ->when($this->priority,   fn($q) => $q->where('priority',    $this->priority))
            ->when($this->type,       fn($q) => $q->where('type',        $this->type))
            ->when($this->customerId, fn($q) => $q->where('customer_id', $this->customerId))
            ->when($this->assignedTo, fn($q) => $q->where('assigned_to', $this->assignedTo))
            ->orderByRaw("FIELD(priority, 'urgent','high','medium','low')")
            ->orderByRaw("FIELD(status, 'open','in_progress','waiting','resolved','closed')")
            ->orderByDesc('created_at')
            ->paginate(25);

        $customers = Customer::where('company_id', $cid)->orderBy('name')->get(['id','name']);
        $users     = User::where('company_id', $cid)->orderBy('name')->get(['id','name']);

        $stats = [
            'open'        => CrmTicket::where('company_id', $cid)->where('status', 'open')->count(),
            'in_progress' => CrmTicket::where('company_id', $cid)->where('status', 'in_progress')->count(),
            'waiting'     => CrmTicket::where('company_id', $cid)->where('status', 'waiting')->count(),
            'overdue'     => CrmTicket::where('company_id', $cid)
                                ->whereIn('status', ['open','in_progress','waiting'])
                                ->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        return view('livewire.sales.crm-ticket-index', compact('tickets', 'customers', 'users', 'stats'));
    }
}
