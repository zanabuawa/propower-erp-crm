<?php

namespace App\Livewire\Branches;

use App\Livewire\Concerns\HasLocationFields;
use App\Models\Branch;
use App\Models\Company;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BranchForm extends Component
{
    use HasLocationFields;
    public ?Branch $branch = null;
    public ?int $company_id = null;
    public string $name = '';
    public string $code = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = 'México';
    public bool $is_active = true;

    public function mount($branch = null): void
    {
        if ($branch) {
            $this->branch = $branch instanceof Branch
                ? $branch
                : Branch::findOrFail($branch);

            $this->company_id = $this->branch->company_id;
            $this->name       = $this->branch->name;
            $this->code       = $this->branch->code ?? '';
            $this->email      = $this->branch->email ?? '';
            $this->phone      = $this->branch->phone ?? '';
            $this->address    = $this->branch->address ?? '';
            $this->city       = $this->branch->city ?? '';
            $this->state      = $this->branch->state ?? '';
            $this->is_active  = $this->branch->is_active;
        }

        $this->initializeLocation();
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'name'       => 'required|string|max:255',
            'code'       => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:100',
            'state'      => 'nullable|string|max:100',
            'is_active'  => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id' => $this->company_id,
            'name'       => $this->name,
            'code'       => $this->code,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => $this->address,
            'city'       => $this->city,
            'state'      => $this->state,
            'is_active'  => $this->is_active,
        ];

        if ($this->branch?->exists) {
            $this->branch->update($data);
            session()->flash('success', 'Sucursal actualizada correctamente.');
        } else {
            Branch::create($data);
            session()->flash('success', 'Sucursal creada correctamente.');
        }

        $this->redirect(route('branches.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.branches.branch-form', [
            'companies' => Company::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}