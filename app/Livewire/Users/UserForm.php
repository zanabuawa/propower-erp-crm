<?php

namespace App\Livewire\Users;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.app')]
class UserForm extends Component
{
    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $company_id = null;
    public ?int $branch_id = null;
    public string $role = '';
    public bool $is_active = true;

    public function mount($user = null): void
    {
        if ($user) {
            $this->user = $user instanceof User
                ? $user
                : User::findOrFail($user);

            $this->name       = $this->user->name;
            $this->email      = $this->user->email;
            $this->company_id = $this->user->company_id;
            $this->branch_id  = $this->user->branch_id;
            $this->is_active  = $this->user->is_active;
            $this->role       = $this->user->roles->first()?->name ?? '';
        }
    }

    public function updatedCompanyId(): void
    {
        $this->branch_id = null;
    }

    public function rules(): array
    {
        $passwordRule = $this->user?->exists
            ? 'nullable|min:8|confirmed'
            : 'required|min:8|confirmed';

        return [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . ($this->user?->id ?? 'NULL'),
            'password'   => $passwordRule,
            'company_id' => 'nullable|exists:companies,id',
            'branch_id'  => 'nullable|exists:branches,id',
            'role'       => 'required|exists:roles,name',
            'is_active'  => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'       => $this->name,
            'email'      => $this->email,
            'company_id' => $this->company_id,
            'branch_id'  => $this->branch_id,
            'is_active'  => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user?->exists) {
            $this->user->update($data);
            $this->user->syncRoles([$this->role]);
            session()->flash('success', 'Usuario actualizado correctamente.');
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);
            session()->flash('success', 'Usuario creado correctamente.');
        }

        $this->redirect(route('users.index'));
    }

    public function render()
    {
        return view('livewire.users.user-form', [
            'companies' => Company::where('is_active', true)->orderBy('name')->get(),
            'branches'  => $this->company_id
                ? Branch::where('company_id', $this->company_id)->where('is_active', true)->orderBy('name')->get()
                : collect(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}