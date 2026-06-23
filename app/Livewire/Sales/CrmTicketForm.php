<?php

namespace App\Livewire\Sales;

use App\Models\CrmTicket;
use App\Models\Customer;
use App\Models\SaleInvoice;
use App\Models\SaleOrder;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmTicketForm extends Component
{
    public ?CrmTicket $ticket = null;

    public string $subject        = '';
    public string $description    = '';
    public string $type           = 'support';
    public string $priority       = 'medium';
    public string $ticket_scope   = 'customer';
    public string $customer_id    = '';
    public string $assigned_to    = '';
    public string $sale_order_id  = '';
    public string $sale_invoice_id= '';
    public string $due_at         = '';

    public function mount(?CrmTicket $ticket = null): void
    {
        if ($ticket && $ticket->exists) {
            $this->ticket           = $ticket;
            $this->subject          = $ticket->subject;
            $this->description      = $ticket->description ?? '';
            $this->type             = $ticket->type;
            $this->priority         = $ticket->priority;
            $this->ticket_scope     = $ticket->customer_id ? 'customer' : 'internal';
            $this->customer_id      = (string) ($ticket->customer_id ?? '');
            $this->assigned_to      = (string) ($ticket->assigned_to ?? '');
            $this->sale_order_id    = (string) ($ticket->sale_order_id ?? '');
            $this->sale_invoice_id  = (string) ($ticket->sale_invoice_id ?? '');
            $this->due_at           = $ticket->due_at?->format('Y-m-d') ?? '';
        } else {
            $this->assigned_to = (string) auth()->id();
            if (request('customer_id')) {
                $this->ticket_scope = 'customer';
                $this->customer_id = request('customer_id');
            }
        }
    }

    public function updatedTicketScope(): void
    {
        if ($this->ticket_scope === 'internal') {
            $this->customer_id = '';
            $this->sale_order_id = '';
            $this->sale_invoice_id = '';
            $this->type = 'internal';
        } elseif ($this->type === 'internal') {
            $this->type = 'support';
        }
    }

    public function updatedCustomerId(): void
    {
        // Limpiar referencias cuando cambia el cliente
        $this->sale_order_id   = '';
        $this->sale_invoice_id = '';
    }

    public function rules(): array
    {
        return [
            'subject'         => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:internal,support,warranty,complaint,inquiry,return',
            'priority'        => 'required|in:low,medium,high,urgent',
            'ticket_scope'    => 'required|in:customer,internal',
            'customer_id'     => 'nullable|exists:customers,id',
            'assigned_to'     => 'nullable|exists:users,id',
            'sale_order_id'   => 'nullable|exists:sale_orders,id',
            'sale_invoice_id' => 'nullable|exists:sale_invoices,id',
            'due_at'          => 'nullable|date',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $cid  = auth()->user()->company_id;

        unset($data['ticket_scope']);

        if ($this->ticket_scope === 'internal') {
            $data['customer_id'] = null;
            $data['sale_order_id'] = null;
            $data['sale_invoice_id'] = null;
        } else {
            $data['customer_id'] = $data['customer_id'] ?: null;
        }

        $data['assigned_to']     = $data['assigned_to']     ?: null;
        $data['sale_order_id']   = $data['sale_order_id']   ?: null;
        $data['sale_invoice_id'] = $data['sale_invoice_id'] ?: null;

        if ($this->ticket?->exists) {
            $this->ticket->update($data);
            session()->flash('success', 'Ticket actualizado.');
        } else {
            $folio = 'TKT-' . str_pad(
                CrmTicket::where('company_id', $cid)->count() + 1,
                5, '0', STR_PAD_LEFT
            );
            CrmTicket::create(array_merge($data, [
                'company_id' => $cid,
                'created_by' => auth()->id(),
                'folio'      => $folio,
                'status'     => 'open',
            ]));
            session()->flash('success', 'Ticket creado correctamente.');
        }

        $this->redirect(route('sales.crm.tickets.index'), navigate: true);
    }

    public function render()
    {
        $cid       = auth()->user()->company_id;
        $customers = Customer::where('company_id', $cid)->where('status', 'active')->orderBy('name')->get();
        $users     = User::where('company_id', $cid)->orderBy('name')->get();

        $orders   = $this->customer_id
            ? SaleOrder::where('company_id', $cid)->where('customer_id', $this->customer_id)->orderByDesc('created_at')->get(['id','folio'])
            : collect();
        $invoices = $this->customer_id
            ? SaleInvoice::where('company_id', $cid)->where('customer_id', $this->customer_id)->orderByDesc('issued_at')->get(['id','folio'])
            : collect();

        return view('livewire.sales.crm-ticket-form', compact('customers', 'users', 'orders', 'invoices'));
    }
}
