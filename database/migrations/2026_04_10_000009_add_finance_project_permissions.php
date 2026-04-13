<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $guard = 'web';
        $now   = now();

        $permissions = [
            // Proyectos
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            // Finanzas
            'view finance',
            'create finance',
            'edit finance',
            'delete finance',
        ];

        $permIds = [];
        foreach ($permissions as $perm) {
            if (!DB::table('permissions')->where('name', $perm)->exists()) {
                $permIds[] = DB::table('permissions')->insertGetId([
                    'name'       => $perm,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Asignar todos los permisos a super-admin y admin
        $roles = DB::table('roles')
            ->whereIn('name', ['super-admin', 'admin'])
            ->pluck('id');

        foreach ($roles as $roleId) {
            foreach ($permIds as $permId) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $permId)
                    ->where('role_id', $roleId)
                    ->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id'       => $roleId,
                    ]);
                }
            }
        }

        // Gerente solo obtiene view
        $viewPerms = DB::table('permissions')
            ->whereIn('name', ['view projects', 'view finance'])
            ->pluck('id');

        $gerente = DB::table('roles')->where('name', 'gerente')->first();
        if ($gerente) {
            foreach ($viewPerms as $permId) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $permId)
                    ->where('role_id', $gerente->id)
                    ->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id'       => $gerente->id,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permissions = [
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'view finance',  'create finance',  'edit finance',  'delete finance',
        ];

        $ids = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
