<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'companies', 'branches', 'users',
            'inventory', 'purchases', 'sales',
            'accounting', 'hr', 'production',
            'projects', 'contacts', 'opportunities',
            'tickets', 'campaigns', 'reports',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}"]);
            }
        }

        Role::firstOrCreate(['name' => 'super-admin']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'gerente']);
        $manager->givePermissionTo(Permission::whereNotIn('name', [
            'create companies', 'edit companies', 'delete companies',
            'delete users', 'delete branches',
        ])->get());

        $employee = Role::firstOrCreate(['name' => 'empleado']);
        $employee->givePermissionTo([
            'view inventory', 'view sales', 'view contacts',
            'view tickets', 'create tickets',
        ]);
    }
}