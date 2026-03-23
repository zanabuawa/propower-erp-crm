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
        $branch  = Branch::first();

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sistema.com'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('password'),
                'company_id' => $company->id,
                'branch_id'  => $branch->id,
                'is_active'  => true,
            ]
        );
        $superAdmin->assignRole('super-admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@miempresa.com'],
            [
                'name'       => 'Administrador',
                'password'   => Hash::make('password'),
                'company_id' => $company->id,
                'branch_id'  => $branch->id,
                'is_active'  => true,
            ]
        );
        $admin->assignRole('admin');
    }
}