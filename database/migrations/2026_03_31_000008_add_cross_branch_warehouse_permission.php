<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $guardName = 'web';
        $permission = 'access other branches warehouses';

        // Spatie permissions table
        $exists = DB::table('permissions')->where('name', $permission)->exists();
        if (!$exists) {
            $now = now();
            $permId = DB::table('permissions')->insertGetId([
                'name'       => $permission,
                'guard_name' => $guardName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Assign to super-admin, admin, gerente roles
            $roles = DB::table('roles')
                ->whereIn('name', ['super-admin', 'admin', 'gerente'])
                ->pluck('id');

            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permId,
                    'role_id'       => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $perm = DB::table('permissions')->where('name', 'access other branches warehouses')->first();
        if ($perm) {
            DB::table('role_has_permissions')->where('permission_id', $perm->id)->delete();
            DB::table('permissions')->where('id', $perm->id)->delete();
        }
    }
};
