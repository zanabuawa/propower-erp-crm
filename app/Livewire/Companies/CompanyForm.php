<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CompanyForm extends Component
{
    use WithFileUploads;

    public ?Company $company = null;
    public string $name = '';
    public string $legal_name = '';
    public string $rfc = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = 'México';
    public bool $is_active = true;
    public $logo;
    public $icon;

    public function mount($company = null): void
    {
        if ($company) {
            $this->company = $company instanceof Company
                ? $company
                : Company::findOrFail($company);

            $this->name = $this->company->name ?? '';
            $this->legal_name = $this->company->legal_name ?? '';
            $this->rfc = $this->company->rfc ?? '';
            $this->email = $this->company->email ?? '';
            $this->phone = $this->company->phone ?? '';
            $this->address = $this->company->address ?? '';
            $this->city = $this->company->city ?? '';
            $this->state = $this->company->state ?? '';
            $this->country = $this->company->country ?? 'México';
            $this->is_active = $this->company->is_active ?? true;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {


        $this->validate();

        $data = [
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'rfc' => $this->rfc,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'is_active' => $this->is_active,
        ];

        if ($this->logo && is_object($this->logo)) {
            $data['logo'] = $this->logo->store('logos', 'public');
        }

        if ($this->icon && is_object($this->icon)) {
            $data['icon'] = $this->icon->store('icons', 'public');
        }

        if ($this->company?->exists) {
            $this->company->update($data);
            session()->flash('success', 'Empresa actualizada correctamente.');
        } else {
            Company::create($data);
            session()->flash('success', 'Empresa creada correctamente.');
        }

        $this->redirect(route('companies.index'));
    }

    public function render()
    {
        return view('livewire.companies.company-form');
    }
}
