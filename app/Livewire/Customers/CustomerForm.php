<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\CustomerPhone;
use App\Models\CustomerEmail;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CustomerForm extends Component
{
    use WithFileUploads;

    public ?Customer $customer = null;
    public string $type = 'company';
    public string $name = '';
    public string $rfc = '';
    public string $tax_regime = '';
    public string $birthdate = '';
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

    public function mount($customer = null): void
    {
        if ($customer) {
            $this->customer       = $customer instanceof Customer ? $customer : Customer::with('phones', 'emails')->findOrFail($customer);
            $this->type           = $this->customer->type;
            $this->name           = $this->customer->name;
            $this->rfc            = $this->customer->rfc ?? '';
            $this->tax_regime     = $this->customer->tax_regime ?? '';
            $this->birthdate      = $this->customer->birthdate?->format('Y-m-d') ?? '';
            $this->anniversary_date = $this->customer->anniversary_date?->format('Y-m-d') ?? '';
            $this->address        = $this->customer->address ?? '';
            $this->city           = $this->customer->city ?? '';
            $this->state          = $this->customer->state ?? '';
            $this->country        = $this->customer->country ?? 'México';
            $this->zip_code       = $this->customer->zip_code ?? '';
            $this->website        = $this->customer->website ?? '';
            $this->credit_limit   = $this->customer->credit_limit;
            $this->payment_terms  = $this->customer->payment_terms;
            $this->status         = $this->customer->status;
            $this->description    = $this->customer->description ?? '';
            $this->phones         = $this->customer->phones->map(fn($p) => [
                'id' => $p->id, 'number' => $p->number, 'type' => $p->type, 'is_primary' => $p->is_primary,
            ])->toArray();
            $this->emails         = $this->customer->emails->map(fn($e) => [
                'id' => $e->id, 'email' => $e->email, 'type' => $e->type, 'is_primary' => $e->is_primary,
            ])->toArray();
        }

        if (empty($this->phones)) {
            $this->phones = [['id' => null, 'number' => '', 'type' => 'mobile', 'is_primary' => true]];
        }
        if (empty($this->emails)) {
            $this->emails = [['id' => null, 'email' => '', 'type' => 'work', 'is_primary' => true]];
        }
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

    public function rules(): array
    {
        return [
            'type'             => 'required|in:person,company',
            'name'             => 'required|string|max:255',
            'rfc'              => 'nullable|string|max:13',
            'tax_regime'       => 'nullable|string|max:255',
            'birthdate'        => 'nullable|date',
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
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'       => auth()->user()->company_id,
            'type'             => $this->type,
            'name'             => $this->name,
            'rfc'              => $this->rfc ?: null,
            'tax_regime'       => $this->tax_regime ?: null,
            'birthdate'        => $this->birthdate ?: null,
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

        session()->flash('success', $this->customer?->exists ? 'Cliente actualizado.' : 'Cliente creado.');
        $this->redirect(route('contacts.index'));
    }

    public function render()
    {
        return view('livewire.customers.customer-form', [

        ]);
    }
}