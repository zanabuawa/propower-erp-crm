<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Columna global_discount_pct en órdenes y cotizaciones ───────────
        Schema::table('sale_orders', function (Blueprint $table) {
            $table->decimal('global_discount_pct', 8, 4)->default(0)->after('discount_amount');
        });

        Schema::table('sale_quotations', function (Blueprint $table) {
            $table->decimal('global_discount_pct', 8, 4)->default(0)->after('discount_amount');
        });

        // ── Permisos de nivel de descuento ───────────────────────────────────
        $guardName = 'web';

        $level2Id = DB::table('permissions')->insertGetId([
            'name'       => 'discount level 2',
            'guard_name' => $guardName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $level3Id = DB::table('permissions')->insertGetId([
            'name'       => 'discount level 3',
            'guard_name' => $guardName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asignar a roles: admin y manager obtienen nivel 3 (ya pueden aprobar)
        $adminRole   = DB::table('roles')->where('name', 'admin')->first();
        $managerRole = DB::table('roles')->where('name', 'manager')->first();

        foreach (array_filter([$adminRole, $managerRole]) as $role) {
            DB::table('role_has_permissions')->insert([
                ['permission_id' => $level2Id, 'role_id' => $role->id],
                ['permission_id' => $level3Id, 'role_id' => $role->id],
            ]);
        }

        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            $table->dropColumn('global_discount_pct');
        });

        Schema::table('sale_quotations', function (Blueprint $table) {
            $table->dropColumn('global_discount_pct');
        });

        $ids = DB::table('permissions')
            ->whereIn('name', ['discount level 2', 'discount level 3'])
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
