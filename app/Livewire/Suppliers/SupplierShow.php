<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use App\Models\SupplierNote;
use App\Models\SupplierContact;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierShow extends Component
{
    public Supplier $supplier;
    public string $activeTab = 'contacts';

    public bool $showNoteForm = false;
    public string $noteType = 'note';
    public string $noteTitle = '';
    public string $noteBody = '';

    public bool $showContactForm = false;
    public string $contactFirstName = '';
    public string $contactLastName = '';
    public string $contactPosition = '';
    public string $contactPhone = '';
    public string $contactEmail = '';
    public bool $contactIsPrimary = false;

    public function mount($supplier): void
    {
        $this->supplier = $supplier instanceof Supplier
            ? $supplier
            : Supplier::with(['phones', 'emails', 'contacts', 'notes.user', 'assignedTo', 'bankAccounts'])->findOrFail($supplier);
    }

    public function saveNote(): void
    {
        $this->validate([
            'noteTitle' => 'required|string|max:255',
            'noteType'  => 'required|in:note,call,email,meeting,task',
            'noteBody'  => 'nullable|string',
        ]);

        $this->supplier->notes()->create([
            'user_id'  => auth()->id(),
            'type'     => $this->noteType,
            'title'    => $this->noteTitle,
            'body'     => $this->noteBody,
            'noted_at' => now(),
        ]);

        $this->reset(['noteTitle', 'noteBody', 'noteType', 'showNoteForm']);
        $this->supplier->load('notes.user');
        session()->flash('success', 'Nota agregada.');
    }

    public function deleteNote(int $id): void
    {
        SupplierNote::findOrFail($id)->delete();
        $this->supplier->load('notes.user');
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

        $this->supplier->contacts()->create([
            'first_name' => $this->contactFirstName,
            'last_name'  => $this->contactLastName,
            'position'   => $this->contactPosition,
            'phone'      => $this->contactPhone,
            'email'      => $this->contactEmail,
            'is_primary' => $this->contactIsPrimary,
        ]);

        $this->reset(['contactFirstName', 'contactLastName', 'contactPosition', 'contactPhone', 'contactEmail', 'contactIsPrimary', 'showContactForm']);
        $this->supplier->load('contacts');
        session()->flash('success', 'Contacto agregado.');
    }

    public function deleteContact(int $id): void
    {
        SupplierContact::findOrFail($id)->delete();
        $this->supplier->load('contacts');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-show');
    }
}
