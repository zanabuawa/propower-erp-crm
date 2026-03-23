<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['rfc' => 'XAXX010101000'],
            [
                'name'       => 'Mi Empresa S.A. de C.V.',
                'legal_name' => 'Mi Empresa Sociedad Anónima de Capital Variable',
                'email'      => 'contacto@miempresa.com',
                'phone'      => '6141234567',
                'address'    => 'Calle Principal 123',
                'city'       => 'Delicias',
                'state'      => 'Chihuahua',
                'country'    => 'México',
                'is_active'  => true,
            ]
        );

        Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'MAT'],
            [
                'name'      => 'Matriz',
                'email'     => 'matriz@miempresa.com',
                'phone'     => '6141234567',
                'address'   => 'Calle Principal 123',
                'city'      => 'Delicias',
                'state'     => 'Chihuahua',
                'is_active' => true,
            ]
        );
    }
}