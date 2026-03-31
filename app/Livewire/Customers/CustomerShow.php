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
        return view('livewire.customers.customer-show');
    }
}