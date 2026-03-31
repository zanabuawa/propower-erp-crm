<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions grouped by module.
     * key   = permission slug prefix
     * value = human-readable label (used in UI)
     */
    public static array $modules = [
        'inventory'  => 'Inventario',
        'purchases'  => 'Compras',
        'sales'      => 'Ventas',
        'contacts'   => 'Clientes / CRM',
        'suppliers'  => 'Proveedores',
        'hr'         => 'Recursos Humanos',
        'accounting' => 'Contabilidad',
        'projects'   => 'Proyectos',
        'reports'    => 'Reportes',
        'companies'  => 'Empresas',
        'branches'   => 'Sucursales',
        'users'      => 'Usuarios',
    ];

    public static array $actions = [
        'view'   => 'Ver',
        'create' => 'Crear',
        'edit'   => 'Editar',
        'delete' => 'Eliminar',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach (array_keys(self::$modules) as $module) {
            foreach (array_keys(self::$actions) as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}"]);
            }
        }

        // super-admin: bypasses all permission checks via gate (no explicit perms needed)
        Role::firstOrCreate(['name' => 'super-admin']);

        // admin: all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // gerente: all except company/user management and deletes on sensitive modules
        $manager = Role::firstOrCreate(['name' => 'gerente']);
        $manager->syncPermissions(
            Permission::whereNotIn('name', [
                'create companies', 'edit companies', 'delete companies',
                'delete users', 'create users', 'edit users',
                'delete branches',
            ])->get()
        );

        // vendedor: sales, crm, view inventory
        $seller = Role::firstOrCreate(['name' => 'vendedor']);
        $seller->syncPermissions([
            'view inventory',
            'view sales', 'create sales', 'edit sales',
            'view contacts', 'create contacts', 'edit contacts',
            'view suppliers',
            'view reports',
        ]);

        // almacenista: inventory and purchases
        $warehouse = Role::firstOrCreate(['name' => 'almacenista']);
        $warehouse->syncPermissions([
            'view inventory', 'create inventory', 'edit inventory',
            'view purchases', 'create purchases', 'edit purchases',
        ]);

        // comprador: purchases full, inventory view
        $buyer = Role::firstOrCreate(['name' => 'comprador']);
        $buyer->syncPermissions([
            'view inventory',
            'view purchases', 'create purchases', 'edit purchases',
            'view suppliers',
        ]);

        // empleado: very limited
        $employee = Role::firstOrCreate(['name' => 'empleado']);
        $employee->syncPermissions([
            'view inventory',
            'view sales',
            'view contacts',
        ]);
    }
}
