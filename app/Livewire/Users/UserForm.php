<?php

namespace App\Livewire\Users;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Permission;
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

    /** Direct extra permissions granted to this user beyond their role */
    public array $selectedPermissions = [];

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

            // Load direct permissions (extras beyond role)
            $this->selectedPermissions = $this->user
                ->getDirectPermissions()
                ->pluck('name')
                ->toArray();
        }
    }

    public function updatedCompanyId(): void
    {
        $this->branch_id = null;
    }

    /**
     * When role changes, reset direct permissions to nothing.
     * Role permissions are shown as "included" in the UI automatically.
     */
    public function updatedRole(): void
    {
        $this->selectedPermissions = [];
    }

    /**
     * Pre-populate selectedPermissions with the role's default set.
     * Called from the view via a button "Cargar permisos del rol".
     */
    public function loadRolePermissions(): void
    {
        if (!$this->role) return;

        $role = Role::findByName($this->role);
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    /**
     * Permissions the current role grants (for display purposes).
     */
    public function getRolePermissionsProperty(): array
    {
        if (!$this->role) return [];
        $role = Role::findByName($this->role);
        return $role ? $role->permissions->pluck('name')->toArray() : [];
    }

    /**
     * All permissions grouped by module with labels.
     */
    public function getGroupedPermissionsProperty(): array
    {
        $groups = [];
        foreach (RolesAndPermissionsSeeder::$modules as $module => $label) {
            $permissions = [];
            foreach (RolesAndPermissionsSeeder::$actions as $action => $actionLabel) {
                $permName = "{$action} {$module}";
                $permissions[] = [
                    'name'        => $permName,
                    'action'      => $action,
                    'actionLabel' => $actionLabel,
                ];
            }
            $groups[] = [
                'module'      => $module,
                'label'       => $label,
                'permissions' => $permissions,
            ];
        }
        return $groups;
    }

    public function rules(): array
    {
        $passwordRule = $this->user?->exists
            ? 'nullable|min:8|confirmed'
            : 'required|min:8|confirmed';

        return [
            'name'                   => 'required|string|max:255',
            'email'                  => 'required|email|unique:users,email,' . ($this->user?->id ?? 'NULL'),
            'password'               => $passwordRule,
            'company_id'             => 'nullable|exists:companies,id',
            'branch_id'              => 'nullable|exists:branches,id',
            'role'                   => 'required|exists:roles,name',
            'is_active'              => 'boolean',
            'selectedPermissions'    => 'array',
            'selectedPermissions.*'  => 'string|exists:permissions,name',
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
            $this->user->syncPermissions($this->selectedPermissions);
            session()->flash('success', 'Usuario actualizado correctamente.');
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);
            $user->syncPermissions($this->selectedPermissions);
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
            'roles'     => Role::orderBy('name')->get(),
        ]);
    }
}
