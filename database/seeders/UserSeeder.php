<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $matriz  = Branch::where('code', 'MAT')->first() ?? Branch::first();
        $norte   = Branch::where('code', 'NTE')->first() ?? $matriz;

        $users = [
            ['email' => 'superadmin@sistema.com', 'name' => 'Super Administrador',    'role' => 'super-admin',  'branch' => $matriz],
            ['email' => 'admin@miempresa.com',     'name' => 'Administrador General',  'role' => 'admin',        'branch' => $matriz],
            ['email' => 'gerente@miempresa.com',   'name' => 'Roberto Valenzuela',     'role' => 'gerente',      'branch' => $matriz],
            ['email' => 'vendedor@miempresa.com',  'name' => 'Carlos Mendoza',         'role' => 'vendedor',     'branch' => $matriz],
            ['email' => 'vendedor2@miempresa.com', 'name' => 'Sofía Ramírez',          'role' => 'vendedor',     'branch' => $norte],
            ['email' => 'comprador@miempresa.com', 'name' => 'Ana Luisa Torres',       'role' => 'comprador',    'branch' => $matriz],
            ['email' => 'almacen@miempresa.com',   'name' => 'Miguel Ángel Soto',      'role' => 'almacenista',  'branch' => $matriz],
            ['email' => 'empleado@miempresa.com',  'name' => 'Luis Enrique Flores',    'role' => 'empleado',     'branch' => $matriz],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'       => $data['name'],
                    'password'   => Hash::make('password'),
                    'company_id' => $company->id,
                    'branch_id'  => $data['branch']->id,
                    'is_active'  => true,
                ]
            );

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
