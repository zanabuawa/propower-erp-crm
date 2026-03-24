<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerContact;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CustomerShow extends Component
{
    public Customer $customer;
    public string $activeTab = 'info';

    // Nota
    public bool $showNoteForm = false;
    public string $noteType = 'note';
    public string $noteTitle = '';
    public string $noteBody = '';

    // Contacto
    public bool $showContactForm = false;
    public string $contactFirstName = '';
    public string $contactLastName = '';
    public string $contactPosition = '';
    public string $contactPhone = '';
    public string $contactEmail = '';
    public bool $contactIsPrimary = false;

    public function mount($customer): void
    {
        $this->customer = $customer instanceof Customer
            ? $customer
            : Customer::with(['phones', 'emails', 'contacts', 'notes.user', 'assignedTo'])->findOrFail($customer);
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
            'contactFirstName' => 'required|string|max:255',
            'contactLastName'  => 'nullable|string|max:255',
            'contactPosition'  => 'nullable|string|max:255',
            'contactPhone'     => 'nullable|string|max:20',
            'contactEmail'     => 'nullable|email|max:255',
        ]);

        $this->customer->contacts()->create([
            'first_name'  => $this->contactFirstName,
            'last_name'   => $this->contactLastName,
            'position'    => $this->contactPosition,
            'phone'       => $this->contactPhone,
            'email'       => $this->contactEmail,
            'is_primary'  => $this->contactIsPrimary,
        ]);

        $this->reset(['contactFirstName', 'contactLastName', 'contactPosition', 'contactPhone', 'contactEmail', 'contactIsPrimary', 'showContactForm']);
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
        return view('livewire.customers.customer-show');
    }
}