<?php

namespace App\Livewire\Customers;

use App\Livewire\Concerns\HasLocationFields;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerPhone;
use App\Models\CustomerEmail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CustomerForm extends Component
{
    use WithFileUploads, HasLocationFields;

    public ?Customer $customer = null;
    public string $name = '';
    public string $rfc = '';
    public string $tax_regime = '';
    public string $anniversary_date = '';
    public $image = null;
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = 'México';
    public string $zip_code = '';
    public string $website = '';
    public string $credit_limit = '0';
    public string $payment_terms = '0';
    public string $status = 'prospect';
    public string $description = '';

    public array $phones = [];
    public array $emails = [];
    public array $contacts = [];

    public function mount($customer = null): void
    {
        if ($customer) {
            $this->customer = $customer instanceof Customer
                ? $customer
                : Customer::with('phones', 'emails', 'contacts')->findOrFail($customer);

            $this->name             = $this->customer->name;
            $this->rfc              = $this->customer->rfc ?? '';
            $this->tax_regime       = $this->customer->tax_regime ?? '';
            $this->anniversary_date = $this->customer->anniversary_date?->format('Y-m-d') ?? '';
            $this->address          = $this->customer->address ?? '';
            $this->city             = $this->customer->city ?? '';
            $this->state            = $this->customer->state ?? '';
            $this->country          = $this->customer->country ?? 'México';
            $this->zip_code         = $this->customer->zip_code ?? '';
            $this->website          = $this->customer->website ?? '';
            $this->credit_limit     = $this->customer->credit_limit;
            $this->payment_terms    = $this->customer->payment_terms;
            $this->status           = $this->customer->status;
            $this->description      = $this->customer->description ?? '';

            $this->phones = $this->customer->phones->map(fn($p) => [
                'id' => $p->id, 'number' => $p->number, 'type' => $p->type, 'is_primary' => $p->is_primary,
            ])->toArray();

            $this->emails = $this->customer->emails->map(fn($e) => [
                'id' => $e->id, 'email' => $e->email, 'type' => $e->type, 'is_primary' => $e->is_primary,
            ])->toArray();

            $this->contacts = $this->customer->contacts->map(fn($c) => [
                'id'               => $c->id,
                'first_name'       => $c->first_name,
                'alias'            => $c->alias ?? '',
                'paternal_surname' => $c->paternal_surname ?? '',
                'maternal_surname' => $c->maternal_surname ?? '',
                'position'         => $c->position ?? '',
                'phone'            => $c->phone ?? '',
                'email'            => $c->email ?? '',
                'is_primary'       => $c->is_primary,
                'description'      => $c->description ?? '',
            ])->toArray();
        }

        if (empty($this->phones)) {
            $this->phones = [['id' => null, 'number' => '', 'type' => 'mobile', 'is_primary' => true]];
        }
        if (empty($this->emails)) {
            $this->emails = [['id' => null, 'email' => '', 'type' => 'work', 'is_primary' => true]];
        }

        $this->initializeLocation();
    }

    public function addPhone(): void
    {
        $this->phones[] = ['id' => null, 'number' => '', 'type' => 'mobile', 'is_primary' => false];
    }

    public function removePhone(int $index): void
    {
        array_splice($this->phones, $index, 1);
        $this->phones = array_values($this->phones);
    }

    public function addEmail(): void
    {
        $this->emails[] = ['id' => null, 'email' => '', 'type' => 'work', 'is_primary' => false];
    }

    public function removeEmail(int $index): void
    {
        array_splice($this->emails, $index, 1);
        $this->emails = array_values($this->emails);
    }

    public function addContact(): void
    {
        $this->contacts[] = [
            'id'               => null,
            'first_name'       => '',
            'alias'            => '',
            'paternal_surname' => '',
            'maternal_surname' => '',
            'position'         => '',
            'phone'            => '',
            'email'            => '',
            'is_primary'       => empty($this->contacts),
            'description'      => '',
        ];
    }

    public function removeContact(int $index): void
    {
        array_splice($this->contacts, $index, 1);
        $this->contacts = array_values($this->contacts);
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'rfc'              => 'nullable|string|max:13',
            'tax_regime'       => 'nullable|string|max:255',
            'anniversary_date' => 'nullable|date',
            'address'          => 'nullable|string|max:255',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'country'          => 'required|string|max:100',
            'zip_code'         => 'nullable|string|max:10',
            'website'          => 'nullable|string|max:255',
            'credit_limit'     => 'required|numeric|min:0',
            'payment_terms'    => 'required|integer|min:0',
            'status'           => 'required|in:active,inactive,prospect',
            'phones.*.number'  => 'nullable|string|max:20',
            'emails.*.email'   => 'nullable|email|max:255',
            'contacts.*.first_name'       => 'required_with:contacts.*.paternal_surname|nullable|string|max:255',
            'contacts.*.alias'            => 'nullable|string|max:100',
            'contacts.*.paternal_surname' => 'nullable|string|max:100',
            'contacts.*.maternal_surname' => 'nullable|string|max:100',
            'contacts.*.phone'            => 'nullable|string|max:20',
            'contacts.*.email'            => 'nullable|email|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'       => auth()->user()->company_id,
            'name'             => $this->name,
            'rfc'              => $this->rfc ?: null,
            'tax_regime'       => $this->tax_regime ?: null,
            'anniversary_date' => $this->anniversary_date ?: null,
            'address'          => $this->address,
            'city'             => $this->city,
            'state'            => $this->state,
            'country'          => $this->country,
            'zip_code'         => $this->zip_code,
            'website'          => $this->website ?: null,
            'credit_limit'     => $this->credit_limit,
            'payment_terms'    => $this->payment_terms,
            'status'           => $this->status,
            'description'      => $this->description,
        ];

        if ($this->customer?->exists) {
            $this->customer->update($data);
            $customer = $this->customer;
        } else {
            $customer = Customer::create($data);
        }

        if (is_object($this->image)) {
            $customer->update(['image' => $this->image->store('customers', 'public')]);
        }

        $customer->phones()->delete();
        foreach ($this->phones as $phone) {
            if (!empty($phone['number'])) {
                $customer->phones()->create($phone);
            }
        }

        $customer->emails()->delete();
        foreach ($this->emails as $email) {
            if (!empty($email['email'])) {
                $customer->emails()->create($email);
            }
        }

        // Sync contacts: update existing, create new, delete removed
        $keptIds = [];
        foreach ($this->contacts as $contactData) {
            if (empty($contactData['first_name'])) continue;

            $payload = [
                'first_name'       => $contactData['first_name'],
                'alias'            => $contactData['alias'] ?: null,
                'paternal_surname' => $contactData['paternal_surname'] ?: null,
                'maternal_surname' => $contactData['maternal_surname'] ?: null,
                'position'         => $contactData['position'] ?: null,
                'phone'            => $contactData['phone'] ?: null,
                'email'            => $contactData['email'] ?: null,
                'is_primary'       => $contactData['is_primary'],
                'description'      => $contactData['description'] ?: null,
            ];

            if (!empty($contactData['id'])) {
                $contact = CustomerContact::find($contactData['id']);
                if ($contact && $contact->customer_id === $customer->id) {
                    $contact->update($payload);
                    $keptIds[] = $contact->id;
                }
            } else {
                $new = $customer->contacts()->create($payload);
                $keptIds[] = $new->id;
            }
        }

        $customer->contacts()->whereNotIn('id', $keptIds)->delete();

        session()->flash('success', $this->customer?->exists ? 'Cliente actualizado.' : 'Cliente creado.');
        $this->redirect(route('contacts.index'));
    }

    public function render()
    {
        return view('livewire.customers.customer-form');
    }
}
