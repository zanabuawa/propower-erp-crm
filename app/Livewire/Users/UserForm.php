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
    public ?string $birth_date = null;
    public ?string $gender = null;
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $company_id = null;
    public ?int $branch_id = null;
    public string $role = '';
    public bool $is_active = true;
    public string $signatureData = ''; // base64 PNG para firma registrada

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
            $this->birth_date = $this->user->birth_date?->format('Y-m-d');
            $this->gender     = $this->user->gender;
            $this->company_id = $this->user->company_id;
            $this->branch_id  = $this->user->branch_id;
            $this->is_active  = (bool) $this->user->is_active;
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
     * Toggle all selectable (non-role) permissions for a module.
     */
    public function toggleModule(array $permissionNames): void
    {
        $rolePerms = $this->rolePermissions;
        $selectable = array_values(array_filter($permissionNames, fn($p) => !in_array($p, $rolePerms)));

        $allSelected = count($selectable) > 0
            && count(array_diff($selectable, $this->selectedPermissions)) === 0;

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $selectable));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $selectable)));
        }
    }

    /**
     * Permissions the current role grants (for display purposes).
     */
    public function getRolePermissionsProperty(): array
    {
        if (!$this->role) return [];
        $role = Role::where('name', $this->role)->first();
        return $role ? $role->permissions->pluck('name')->toArray() : [];
    }

    /**
     * All permissions grouped by module with labels (CRUD + extras + standalone groups).
     */
    public function getGroupedPermissionsProperty(): array
    {
        $groups = [];

        // Modules with CRUD base + optional granular extras
        foreach (RolesAndPermissionsSeeder::$modules as $module => $label) {
            $permissions = [];

            foreach (RolesAndPermissionsSeeder::$sectionPermissions[$module] ?? [] as $permName => $actionLabel) {
                $permissions[] = [
                    'name'        => $permName,
                    'actionLabel' => $actionLabel,
                    'type'        => 'section',
                ];
            }

            foreach (RolesAndPermissionsSeeder::$actions as $action => $actionLabel) {
                $permissions[] = [
                    'name'        => "{$action} {$module}",
                    'actionLabel' => $actionLabel,
                    'type'        => 'crud',
                ];
            }

            foreach (RolesAndPermissionsSeeder::$extraPermissions[$module] ?? [] as $permName => $actionLabel) {
                $permissions[] = [
                    'name'        => $permName,
                    'actionLabel' => $actionLabel,
                    'type'        => 'extra',
                ];
            }

            $groups[] = ['module' => $module, 'label' => $label, 'permissions' => $permissions];
        }

        // Standalone groups: only granular permissions, no CRUD base (e.g. dashboard)
        foreach (RolesAndPermissionsSeeder::$standaloneGroups as $module => $label) {
            $permissions = [];
            foreach (RolesAndPermissionsSeeder::$sectionPermissions[$module] ?? [] as $permName => $actionLabel) {
                $permissions[] = [
                    'name'        => $permName,
                    'actionLabel' => $actionLabel,
                    'type'        => 'section',
                ];
            }
            foreach (RolesAndPermissionsSeeder::$extraPermissions[$module] ?? [] as $permName => $actionLabel) {
                $permissions[] = [
                    'name'        => $permName,
                    'actionLabel' => $actionLabel,
                    'type'        => 'extra',
                ];
            }
            if (!empty($permissions)) {
                $groups[] = ['module' => $module, 'label' => $label, 'permissions' => $permissions];
            }
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
            'birth_date'             => 'nullable|date',
            'gender'                 => 'nullable|string|max:30',
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
            'birth_date' => $this->birth_date,
            'gender'     => $this->gender,
            'company_id' => $this->company_id,
            'branch_id'  => $this->branch_id,
            'is_active'  => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user?->exists) {
            $this->user->update($data);

            // Sync with HrEmployee if linked
            $employee = \App\Models\HrEmployee::where('user_id', $this->user->id)->first();
            if ($employee) {
                $employee->update([
                    'birth_date' => $this->birth_date,
                    'gender'     => $this->gender,
                ]);
            }

            $this->user->syncRoles([$this->role]);
            $this->user->syncPermissions($this->selectedPermissions);
            session()->flash('success', 'Usuario actualizado correctamente.');
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);
            $user->syncPermissions($this->selectedPermissions);
            session()->flash('success', 'Usuario creado correctamente.');
        }

        $this->redirect(route('users.index'), navigate: true);
    }

    public function saveSignature(): void
    {
        if (empty($this->signatureData)) {
            $this->addError('signatureData', 'Dibuja tu firma antes de guardar.');
            return;
        }

        $target = $this->user ?? auth()->user();
        $target->update([
            'signature'            => $this->signatureData,
            'signature_updated_at' => now(),
        ]);

        $this->signatureData = '';
        session()->flash('signatureSuccess', 'Firma guardada correctamente.');
    }

    public function clearSignature(): void
    {
        $target = $this->user ?? auth()->user();
        $target->update([
            'signature'            => null,
            'signature_updated_at' => null,
        ]);

        session()->flash('signatureSuccess', 'Firma eliminada.');
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
