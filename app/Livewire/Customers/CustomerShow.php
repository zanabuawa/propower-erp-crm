<?php

namespace App\Livewire\Customers;

use App\Models\CrmActivity;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerContact;
use App\Models\SaleInvoice;
use App\Models\SaleOrder;
use App\Models\SaleQuotation;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CustomerShow extends Component
{
    public Customer $customer;
    public string $activeTab = 'info';

    // Segmentation
    public string $segment          = '';
    public string $zone             = '';
    public string $customer_category= '';
    public string $annual_revenue   = '';
    public bool   $editingSegment   = false;

    // Nota
    public bool $showNoteForm = false;
    public string $noteType = 'note';
    public string $noteTitle = '';
    public string $noteBody = '';

    // Contacto
    public bool $showContactForm = false;
    public string $contactFirstName = '';
    public string $contactAlias = '';
    public string $contactPaternalSurname = '';
    public string $contactMaternalSurname = '';
    public string $contactPosition = '';
    public string $contactPhone = '';
    public string $contactEmail = '';
    public bool $contactIsPrimary = false;
    public string $contactDescription = '';

    public function mount($customer): void
    {
        $this->customer = $customer instanceof Customer
            ? $customer
            : Customer::with(['phones', 'emails', 'contacts', 'notes.user', 'assignedTo'])->findOrFail($customer);

        $this->segment           = $this->customer->segment ?? '';
        $this->zone              = $this->customer->zone ?? '';
        $this->customer_category = $this->customer->customer_category ?? '';
        $this->annual_revenue    = $this->customer->annual_revenue ? (string) $this->customer->annual_revenue : '';
    }

    public function saveSegmentation(): void
    {
        $this->validate([
            'segment'           => 'nullable|in:A,B,C,D',
            'zone'              => 'nullable|string|max:100',
            'customer_category' => 'nullable|string|max:50',
            'annual_revenue'    => 'nullable|numeric|min:0',
        ]);

        $this->customer->update([
            'segment'           => $this->segment ?: null,
            'zone'              => $this->zone ?: null,
            'customer_category' => $this->customer_category ?: null,
            'annual_revenue'    => $this->annual_revenue ?: null,
        ]);

        $this->editingSegment = false;
        $this->customer->refresh();
        session()->flash('success', 'Segmentación actualizada.');
    }

    public function saveNote(): void
    {
        $this->validate([
            'noteTitle' => 'required|string|max:255',
            'noteType'  => 'required|in:note,call,email,meeting,task',
            'noteBody'  => 'nullable|string',
        ]);

        $this->customer->notes()->create([
            'user_id'  => auth()->id(),
            'type'     => $this->noteType,
            'title'    => $this->noteTitle,
            'body'     => $this->noteBody,
            'noted_at' => now(),
        ]);

        $this->reset(['noteTitle', 'noteBody', 'noteType', 'showNoteForm']);
        $this->customer->load('notes.user');
        session()->flash('success', 'Nota agregada.');
    }

    public function deleteNote(int $id): void
    {
        CustomerNote::findOrFail($id)->delete();
        $this->customer->load('notes.user');
    }

    public function saveContact(): void
    {
        $this->validate([
            'contactFirstName'       => 'required|string|max:255',
            'contactAlias'           => 'nullable|string|max:100',
            'contactPaternalSurname' => 'nullable|string|max:100',
            'contactMaternalSurname' => 'nullable|string|max:100',
            'contactPosition'        => 'nullable|string|max:255',
            'contactPhone'           => 'nullable|string|max:20',
            'contactEmail'           => 'nullable|email|max:255',
            'contactDescription'     => 'nullable|string',
        ]);

        $this->customer->contacts()->create([
            'first_name'       => $this->contactFirstName,
            'alias'            => $this->contactAlias ?: null,
            'paternal_surname' => $this->contactPaternalSurname ?: null,
            'maternal_surname' => $this->contactMaternalSurname ?: null,
            'position'         => $this->contactPosition ?: null,
            'phone'            => $this->contactPhone ?: null,
            'email'            => $this->contactEmail ?: null,
            'is_primary'       => $this->contactIsPrimary,
            'description'      => $this->contactDescription ?: null,
        ]);

        $this->reset(['contactFirstName', 'contactAlias', 'contactPaternalSurname', 'contactMaternalSurname', 'contactPosition', 'contactPhone', 'contactEmail', 'contactIsPrimary', 'contactDescription', 'showContactForm']);
        $this->customer->load('contacts');
        session()->flash('success', 'Contacto agregado.');
    }

    public function deleteContact(int $id): void
    {
        CustomerContact::findOrFail($id)->delete();
        $this->customer->load('contacts');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $quotations = SaleQuotation::where('customer_id', $this->customer->id)
            ->orderByDesc('created_at')->limit(10)->get();
        $orders = SaleOrder::where('customer_id', $this->customer->id)
            ->orderByDesc('created_at')->limit(10)->get();
        $invoices = SaleInvoice::where('customer_id', $this->customer->id)
            ->orderByDesc('created_at')->limit(10)->get();
        $activities = CrmActivity::where('customer_id', $this->customer->id)
            ->with(['user', 'assignedTo'])->orderByDesc('scheduled_at')->limit(20)->get();

        $commercialStats = [
            'total_invoiced'  => SaleInvoice::where('customer_id', $this->customer->id)->sum('total'),
            'total_orders'    => SaleOrder::where('customer_id', $this->customer->id)->count(),
            'total_quotes'    => SaleQuotation::where('customer_id', $this->customer->id)->count(),
            'pending_invoices' => SaleInvoice::where('customer_id', $this->customer->id)
                ->whereIn('status', ['draft', 'sent'])->count(),
        ];

        return view('livewire.customers.customer-show', compact(
            'quotations', 'orders', 'invoices', 'activities', 'commercialStats'
        ));
    }
}