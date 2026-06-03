<?php

namespace App\Livewire\Sales;

use App\Models\CrmTicket;
use App\Models\CrmTicketMessage;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmTicketShow extends Component
{
    public CrmTicket $ticket;

    public string $messageBody    = '';
    public bool   $isInternal     = false;
    public string $newStatus      = '';
    public string $newAssignedTo  = '';

    public function mount(CrmTicket $ticket): void
    {
        $this->ticket = $ticket->load([
            'customer', 'assignedTo', 'createdBy',
            'messages.user', 'saleOrder', 'saleInvoice',
        ]);
        $this->newStatus     = $ticket->status;
        $this->newAssignedTo = (string) ($ticket->assigned_to ?? '');
    }

    public function sendMessage(): void
    {
        $this->validate(['messageBody' => 'required|string|max:5000']);

        CrmTicketMessage::create([
            'ticket_id'   => $this->ticket->id,
            'user_id'     => auth()->id(),
            'body'        => $this->messageBody,
            'is_internal' => $this->isInternal,
        ]);

        // Cambiar estado automáticamente al responder
        if ($this->ticket->status === 'open') {
            $this->ticket->update(['status' => 'in_progress']);
        }

        $this->reset(['messageBody', 'isInternal']);
        $this->ticket->load('messages.user');
        $this->ticket->refresh();
        $this->newStatus = $this->ticket->status;
    }

    public function updateStatus(): void
    {
        $updates = ['status' => $this->newStatus];

        if ($this->newStatus === 'resolved' && !$this->ticket->resolved_at) {
            $updates['resolved_at'] = now();
        }
        if ($this->newStatus === 'closed' && !$this->ticket->closed_at) {
            $updates['closed_at'] = now();
        }
        if (in_array($this->newStatus, ['open', 'in_progress', 'waiting'])) {
            $updates['resolved_at'] = null;
            $updates['closed_at']   = null;
        }

        $this->ticket->update($updates);
        $this->ticket->refresh();
    }

    public function updateAssigned(): void
    {
        $this->ticket->update([
            'assigned_to' => $this->newAssignedTo ?: null,
        ]);
        $this->ticket->load('assignedTo');
    }

    public function render()
    {
        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        return view('livewire.sales.crm-ticket-show', compact('users'));
    }
}
