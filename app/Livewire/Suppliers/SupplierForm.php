<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierForm extends Component
{
    use WithFileUploads;

    public ?Supplier $supplier = null;
    public string $type = 'company';
    public string $name = '';
    public string $rfc = '';
    public string $tax_regime = '';
    public $image = null;
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = 'México';
    public string $zip_code = '';
    public string $website = '';
    public string $credit_limit = '0';
    public string $payment_terms = '0';
    public string $status = 'active';
    public string $description = '';
    public array $phones = [];
    public array $emails = [];
    public array $bankAccounts = [];

    public function mount($supplier = null): void
    {
        if ($supplier) {
            $this->supplier = $supplier instanceof Supplier
                ? $supplier
                : Supplier::with('phones', 'emails', 'bankAccounts')->findOrFail($supplier);

            $this->type          = $this->supplier->type;
            $this->name          = $this->supplier->name;
            $this->rfc           = $this->supplier->rfc ?? '';
            $this->tax_regime    = $this->supplier->tax_regime ?? '';
            $this->address       = $this->supplier->address ?? '';
            $this->city          = $this->supplier->city ?? '';
            $this->state         = $this->supplier->state ?? '';
            $this->country       = $this->supplier->country ?? 'México';
            $this->zip_code      = $this->supplier->zip_code ?? '';
            $this->website       = $this->supplier->website ?? '';
            $this->credit_limit  = $this->supplier->credit_limit;
            $this->payment_terms = $this->supplier->payment_terms;
            $this->status        = $this->supplier->status;
            $this->description   = $this->supplier->description ?? '';

            $this->phones = $this->supplier->phones->map(fn($p) => [
                'id'         => $p->id,
                'number'     => $p->number,
                'type'       => $p->type,
                'is_primary' => $p->is_primary,
            ])->toArray();

            $this->emails = $this->supplier->emails->map(fn($e) => [
                'id'         => $e->id,
                'email'      => $e->email,
                'type'       => $e->type,
                'is_primary' => $e->is_primary,
            ])->toArray();

            $this->bankAccounts = $this->supplier->bankAccounts->map(fn($b) => [
                'id'             => $b->id,
                'bank_name'      => $b->bank_name,
                'account_number' => $b->account_number ?? '',
                'clabe'          => $b->clabe ?? '',
                'beneficiary'    => $b->beneficiary ?? '',
                'is_primary'     => $b->is_primary,
            ])->toArray();
        }

        if (empty($this->phones)) {
            $this->phones = [['id' => null, 'number' => '', 'type' => 'mobile', 'is_primary' => true]];
        }
        if (empty($this->emails)) {
            $this->emails = [['id' => null, 'email' => '', 'type' => 'work', 'is_primary' => true]];
        }
        if (empty($this->bankAccounts)) {
            $this->bankAccounts = [['id' => null, 'bank_name' => '', 'account_number' => '', 'clabe' => '', 'beneficiary' => '', 'is_primary' => true]];
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

    public function addBankAccount(): void
    {
        $this->bankAccounts[] = ['id' => null, 'bank_name' => '', 'account_number' => '', 'clabe' => '', 'beneficiary' => '', 'is_primary' => false];
    }

    public function removeBankAccount(int $index): void
    {
        array_splice($this->bankAccounts, $index, 1);
        $this->bankAccounts = array_values($this->bankAccounts);
    }

    public function rules(): array
    {
        return [
            'type'                          => 'required|in:person,company',
            'name'                          => 'required|string|max:255',
            'rfc'                           => 'nullable|string|max:13',
            'tax_regime'                    => 'nullable|string|max:255',
            'address'                       => 'nullable|string|max:255',
            'city'                          => 'nullable|string|max:100',
            'state'                         => 'nullable|string|max:100',
            'country'                       => 'required|string|max:100',
            'zip_code'                      => 'nullable|string|max:10',
            'website'                       => 'nullable|string|max:255',
            'credit_limit'                  => 'required|numeric|min:0',
            'payment_terms'                 => 'required|integer|min:0',
            'status'                        => 'required|in:active,inactive',
            'phones.*.number'               => 'nullable|string|max:20',
            'emails.*.email'                => 'nullable|email|max:255',
            'bankAccounts.*.bank_name'      => 'nullable|string|max:255',
            'bankAccounts.*.account_number' => 'nullable|string|max:30',
            'bankAccounts.*.clabe'          => 'nullable|string|max:18',
            'bankAccounts.*.beneficiary'    => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'    => auth()->user()->company_id,
            'type'          => $this->type,
            'name'          => $this->name,
            'rfc'           => $this->rfc ?: null,
            'tax_regime'    => $this->tax_regime ?: null,
            'address'       => $this->address,
            'city'          => $this->city,
            'state'         => $this->state,
            'country'       => $this->country,
            'zip_code'      => $this->zip_code,
            'website'       => $this->website ?: null,
            'credit_limit'  => $this->credit_limit,
            'payment_terms' => $this->payment_terms,
            'status'        => $this->status,
            'description'   => $this->description,
        ];

        if ($this->supplier?->exists) {
            $this->supplier->update($data);
            $supplier = $this->supplier;
        } else {
            $supplier = Supplier::create($data);
        }

        if (is_object($this->image)) {
            $supplier->update(['image' => $this->image->store('suppliers', 'public')]);
        }

        $supplier->phones()->delete();
        foreach ($this->phones as $phone) {
            if (!empty($phone['number'])) {
                $supplier->phones()->create($phone);
            }
        }

        $supplier->emails()->delete();
        foreach ($this->emails as $email) {
            if (!empty($email['email'])) {
                $supplier->emails()->create($email);
            }
        }

        $supplier->bankAccounts()->delete();
        foreach ($this->bankAccounts as $account) {
            if (!empty($account['bank_name'])) {
                $supplier->bankAccounts()->create($account);
            }
        }

        session()->flash('success', $this->supplier?->exists ? 'Proveedor actualizado.' : 'Proveedor creado.');
        $this->redirect(route('suppliers.index'));
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-form');
    }
}