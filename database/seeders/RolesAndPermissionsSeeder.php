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
        'assets'     => 'Activos Fijos',
        'purchases'  => 'Compras',
        'sales'      => 'Ventas',
        'contacts'   => 'Clientes / CRM',
        'suppliers'  => 'Proveedores',
        'hr'         => 'Recursos Humanos',
        'projects'   => 'Proyectos',
        'tenders'    => 'Licitaciones',
        'finance'    => 'Finanzas',
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
     * Groups that only have granular permissions (no CRUD base).
     * key   = group slug, value = human-readable label (used in UI)
     */
    public static array $standaloneGroups = [
        'dashboard' => 'Dashboard',
    ];

    /**
     * Extra granular permissions beyond basic CRUD, grouped by module.
     * key   = permission name
     * value = human-readable label (used in UI)
     */
    public static array $extraPermissions = [

        // ── Inventario ────────────────────────────────────────────────────────
        'inventory' => [
            // Existentes
            'adjust inventory'                 => 'Ajustar stock manualmente',
            'view prices'                      => 'Ver precio de venta de productos',
            'edit product prices'              => 'Modificar precios de productos',
            // Nuevos
            'view product cost'                => 'Ver precio de compra / costo de obtención',
            'view product margin'              => 'Ver margen de utilidad de productos',
            'manage warehouses'                => 'Crear y editar almacenes',
            'manage inventory categories'      => 'Crear y editar categorías de inventario',
            'view inventory analytics'         => 'Ver análisis de rotación, demanda y reabastecimiento',
            'access other branches warehouses' => 'Acceder a almacenes de otras sucursales',
        ],

        // ── Activos Fijos ─────────────────────────────────────────────────────
        'assets' => [
            // Existentes
            'transfer assets'       => 'Transferir activos fijos entre responsables / sucursales',
            // Nuevos
            'manage asset maintenance' => 'Registrar y editar mantenimientos de activos',
            'manage asset loans'       => 'Gestionar préstamos de activos a empleados',
            'depreciate assets'        => 'Registrar y calcular depreciación de activos',
        ],

        // ── Compras ───────────────────────────────────────────────────────────
        'purchases' => [
            // Existentes
            'receive goods'           => 'Recibir mercancía en almacén',
            'approve requisitions'    => 'Aprobar requisiciones de compra',
            // Nuevos
            'approve purchase orders'  => 'Autorizar órdenes de compra',
            'manage supplier invoices' => 'Registrar y gestionar facturas de proveedor',
            'view purchases analytics' => 'Ver analíticas y reportes de compras',
        ],

        // ── Ventas ────────────────────────────────────────────────────────────
        'sales' => [
            // Existentes
            'stamp invoices'       => 'Timbrar CFDI de venta (Facturapi)',
            'cancel invoices'      => 'Cancelar CFDI de venta ante el SAT',
            'override sale prices' => 'Modificar precios unitarios en una venta',
            'apply discounts'      => 'Aplicar descuentos en cotizaciones y órdenes',
            'manage price lists'   => 'Crear y editar listas de precios',
            // Nuevos
            'approve discounts'    => 'Autorizar descuentos solicitados por vendedores',
            'manage crm'           => 'Gestionar prospectos y oportunidades en CRM',
            'view crm analytics'   => 'Ver analíticas de CRM y comportamiento de clientes',
            'manage deliveries'    => 'Crear y gestionar remisiones / entregas',
        ],

        // ── Recursos Humanos ──────────────────────────────────────────────────
        'hr' => [
            // Existentes
            'manage payroll'       => 'Calcular y gestionar nómina',
            'approve leaves'       => 'Aprobar solicitudes de permisos y bajas',
            'stamp payroll'        => 'Timbrar CFDI de nómina (Facturapi)',
            'view payroll'         => 'Ver detalle de nómina de empleados',
            // Nuevos
            'view employee salary'        => 'Ver salario, SDI e historial de nómina individual',
            'view employee sensitive data' => 'Ver RFC, NSS, CURP y datos bancarios (CLABE)',
            'view hr analytics'           => 'Ver indicadores y analíticas de RRHH',
            'manage attendance'           => 'Registrar asistencias y gestionar zonas de asistencia',
            'manage job openings'         => 'Publicar y gestionar vacantes',
            'manage payroll concepts'     => 'Crear y editar conceptos de nómina (percepciones/deducciones)',
            'manage workforce planning'   => 'Gestionar plantilla y planificación de personal',
            'view org chart'              => 'Ver organigrama de la empresa',
            'manage training costs'       => 'Registrar y gestionar costos de capacitación',
        ],

        // ── Proyectos ─────────────────────────────────────────────────────────
        'projects' => [
            // Nuevos
            'manage project expenses' => 'Registrar y gestionar gastos de proyectos',
            'manage project tasks'    => 'Gestionar tareas y tablero kanban de proyectos',
            'view project analytics'  => 'Ver analíticas y avance de proyectos',
            'view project financials' => 'Ver ingresos, costos, utilidad y margen de proyectos',
        ],

        // ── Licitaciones ──────────────────────────────────────────────────────
        'tenders' => [
            // Existentes
            'manage tender catalog' => 'Gestionar catálogo de precios unitarios (APU)',
            'evaluate tenders'      => 'Evaluar y calificar licitaciones',
            'manage work permits'   => 'Gestionar permisos y licencias de trabajo en obra',
            'manage work reports'   => 'Crear y aprobar reportes semanales de avance de obra',
            'approve libranzas'     => 'Aprobar libranzas y estimaciones de obra',
            'view tender analytics' => 'Ver analíticas e indicadores de licitaciones',
            // Nuevos
            'manage site visits'     => 'Registrar y gestionar visitas de campo',
            'manage photo reports'   => 'Subir y gestionar reportes fotográficos de obra',
            'manage work program'    => 'Gestionar programa de obra y cronograma',
            'view tender financials' => 'Ver presupuestos, costos unitarios y márgenes en licitaciones',
        ],

        // ── Finanzas ──────────────────────────────────────────────────────────
        'finance' => [
            // Nuevos
            'approve transactions'     => 'Aprobar transacciones financieras',
            'close period'             => 'Realizar cierre de período contable mensual',
            'reconcile bank'           => 'Realizar conciliación bancaria',
            'send payment reminders'   => 'Enviar recordatorios de pago a clientes',
            'manage collections'       => 'Gestionar cobranza y seguimiento de cuentas',
            'view accounts payable'    => 'Ver cuentas por pagar y antigüedad de saldos CXP',
            'view accounts receivable' => 'Ver cuentas por cobrar y antigüedad de saldos CXC',
            'manage travel expenses'   => 'Registrar, aprobar y pagar viáticos',
            'view employee travel'     => 'Ver viáticos asignados a empleados (RRHH)',
        ],

        // ── Reportes ──────────────────────────────────────────────────────────
        'reports' => [
            // Existentes
            'export reports' => 'Exportar reportes a Excel / PDF',
        ],

        // ── Usuarios ──────────────────────────────────────────────────────────
        'users' => [
            // Existentes
            'manage permissions' => 'Gestionar permisos y roles de usuarios',
        ],

        // ── Dashboard ─────────────────────────────────────────────────────────
        'dashboard' => [
            // Existentes
            'view sales summary'     => 'Ver resumen de ventas en dashboard',
            'view purchases summary' => 'Ver resumen de compras en dashboard',
            'view inventory summary' => 'Ver resumen de inventario en dashboard',
            'view finance summary'   => 'Ver resumen de cobranza en dashboard',
            // Nuevos
            'view hr summary'        => 'Ver resumen de RRHH en dashboard',
            'view projects summary'  => 'Ver resumen de proyectos en dashboard',
            'view tenders summary'   => 'Ver resumen de licitaciones en dashboard',
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

        // gerente: todo excepto administración de empresas, usuarios y permisos
        $manager = Role::firstOrCreate(['name' => 'gerente']);
        $manager->syncPermissions(
            Permission::whereNotIn('name', [
                'create companies', 'edit companies', 'delete companies',
                'create users', 'edit users', 'delete users',
                'delete branches',
                'manage permissions',
            ])->get()
        );

        // vendedor: ventas + CRM + inventario lectura + precios venta + facturación + cobranza lectura
        $seller = Role::firstOrCreate(['name' => 'vendedor']);
        $seller->syncPermissions([
            'view inventory',
            'view prices',           // precio de venta
            'view sales', 'create sales', 'edit sales',
            'view contacts', 'create contacts', 'edit contacts',
            'view suppliers',
            'view reports',
            'stamp invoices',
            'apply discounts',
            'manage crm',
            'view crm analytics',
            'manage deliveries',
            'view sales summary',
            'view finance summary',
            'view accounts receivable',
        ]);

        // almacenista: inventario completo + precios de compra + compras + activos
        $warehouse = Role::firstOrCreate(['name' => 'almacenista']);
        $warehouse->syncPermissions([
            'view inventory', 'create inventory', 'edit inventory',
            'adjust inventory',
            'view prices',
            'view product cost',     // puede ver costo de obtención
            'manage warehouses',
            'manage inventory categories',
            'view inventory analytics',
            'access other branches warehouses',
            'view purchases', 'create purchases', 'edit purchases',
            'receive goods',
            'view assets', 'create assets', 'edit assets', 'transfer assets',
            'manage asset maintenance',
            'manage asset loans',
            'view inventory summary',
            'view purchases summary',
        ]);

        // comprador: gestión completa de compras + costos + aprobaciones + proveedores
        $buyer = Role::firstOrCreate(['name' => 'comprador']);
        $buyer->syncPermissions([
            'view inventory',
            'view prices',
            'view product cost',     // comprador necesita ver costo de obtención
            'view purchases', 'create purchases', 'edit purchases',
            'receive goods',
            'approve requisitions',
            'approve purchase orders',
            'manage supplier invoices',
            'view purchases analytics',
            'view suppliers',
            'view reports',
            'export reports',
            'view purchases summary',
            'view inventory summary',
            'view accounts payable',
        ]);

        // rrhh: gestión completa de RRHH + salarios + datos sensibles + nómina
        $hrManager = Role::firstOrCreate(['name' => 'rrhh']);
        $hrManager->syncPermissions([
            'view hr', 'create hr', 'edit hr',
            'manage payroll', 'approve leaves', 'view payroll', 'stamp payroll',
            'view employee salary',         // puede ver salarios individuales
            'view employee sensitive data', // puede ver RFC/NSS/CURP/CLABE
            'view hr analytics',
            'manage attendance',
            'manage job openings',
            'manage payroll concepts',
            'manage workforce planning',
            'view org chart',
            'manage training costs',
            'view inventory',
            'view hr summary',
        ]);

        // empleado: lectura básica + portal propio
        $employee = Role::firstOrCreate(['name' => 'empleado']);
        $employee->syncPermissions([
            'view inventory',
            'view sales',
            'view contacts',
            'view org chart',
        ]);
    }
}
