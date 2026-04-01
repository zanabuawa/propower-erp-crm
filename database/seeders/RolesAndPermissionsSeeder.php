<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Base CRUD modules.
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

    /**
     * Extra granular permissions beyond basic CRUD, grouped by module.
     * key   = permission name
     * value = human-readable label (used in UI)
     */
    public static array $extraPermissions = [
        'inventory' => [
            'adjust inventory'     => 'Ajustar stock',
            'edit product prices'  => 'Modificar precios',
        ],
        'purchases' => [
            'receive goods'        => 'Recibir mercancía',
            'approve requisitions' => 'Aprobar requisiciones',
        ],
        'sales' => [
            'stamp invoices'       => 'Timbrar CFDI',
            'cancel invoices'      => 'Cancelar CFDI',
            'override sale prices' => 'Modificar precios en venta',
            'apply discounts'      => 'Aplicar descuentos',
            'manage price lists'   => 'Gestionar listas de precios',
        ],
        'reports' => [
            'export reports'       => 'Exportar reportes',
        ],
        'users' => [
            'manage permissions'   => 'Gestionar permisos',
        ],
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create base CRUD permissions
        foreach (array_keys(self::$modules) as $module) {
            foreach (array_keys(self::$actions) as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}"]);
            }
        }

        // Create extra granular permissions
        foreach (self::$extraPermissions as $perms) {
            foreach (array_keys($perms) as $permName) {
                Permission::firstOrCreate(['name' => $permName]);
            }
        }

        // ── Roles ─────────────────────────────────────────────────────────────

        // super-admin: bypasses all permission checks via gate (no explicit perms needed)
        Role::firstOrCreate(['name' => 'super-admin']);

        // admin: all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // gerente: all except company management and user/permission administration
        $manager = Role::firstOrCreate(['name' => 'gerente']);
        $manager->syncPermissions(
            Permission::whereNotIn('name', [
                'create companies', 'edit companies', 'delete companies',
                'create users', 'edit users', 'delete users',
                'delete branches',
                'manage permissions',
            ])->get()
        );

        // vendedor: sales + crm + view inventory + invoice actions
        $seller = Role::firstOrCreate(['name' => 'vendedor']);
        $seller->syncPermissions([
            'view inventory',
            'view sales', 'create sales', 'edit sales',
            'view contacts', 'create contacts', 'edit contacts',
            'view suppliers',
            'view reports',
            'stamp invoices',
            'apply discounts',
        ]);

        // almacenista: inventory management + purchases + stock adjustments + receiving
        $warehouse = Role::firstOrCreate(['name' => 'almacenista']);
        $warehouse->syncPermissions([
            'view inventory', 'create inventory', 'edit inventory',
            'adjust inventory',
            'view purchases', 'create purchases', 'edit purchases',
            'receive goods',
        ]);

        // comprador: purchase management + inventory view + receiving + approvals
        $buyer = Role::firstOrCreate(['name' => 'comprador']);
        $buyer->syncPermissions([
            'view inventory',
            'view purchases', 'create purchases', 'edit purchases',
            'receive goods',
            'approve requisitions',
            'view suppliers',
            'view reports',
            'export reports',
        ]);

        // empleado: read-only on core modules
        $employee = Role::firstOrCreate(['name' => 'empleado']);
        $employee->syncPermissions([
            'view inventory',
            'view sales',
            'view contacts',
        ]);
    }
}
