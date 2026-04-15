<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $company  = Company::first();
        $vendedor = User::where('email', 'vendedor@miempresa.com')->first();

        $customers = [
            [
                'name'          => 'Constructora Pérez e Hijos S.A. de C.V.',
                'rfc'           => 'CPH850215FFF',
                'tax_regime'    => '601',
                'cfdi_use'      => 'G03',
                'address'       => 'Av. Tecnológico 1200',
                'city'          => 'Chihuahua',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'zip_code'      => '31000',
                'credit_limit'  => 150000,
                'payment_terms' => 30,
                'status'        => 'active',
                'description'   => 'Empresa constructora con proyectos industriales en el estado.',
            ],
            [
                'name'          => 'Industrias Metálicas del Norte S.A. de C.V.',
                'rfc'           => 'IMN920710GGG',
                'tax_regime'    => '601',
                'cfdi_use'      => 'G01',
                'address'       => 'Parque Industrial Chihuahua Km 5',
                'city'          => 'Chihuahua',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'zip_code'      => '31109',
                'credit_limit'  => 200000,
                'payment_terms' => 45,
                'status'        => 'active',
            ],
            [
                'name'          => 'Municipio de Delicias',
                'rfc'           => 'MDE470101HHH',
                'tax_regime'    => '603',
                'cfdi_use'      => 'G03',
                'address'       => 'Palacio Municipal, calle 3ra 100',
                'city'          => 'Delicias',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'zip_code'      => '33000',
                'credit_limit'  => 500000,
                'payment_terms' => 60,
                'status'        => 'active',
                'description'   => 'Entidad gubernamental. Pago mediante orden de compra oficial.',
            ],
            [
                'name'          => 'Fraccionadora Los Pinos S.A. de C.V.',
                'rfc'           => 'FLP001201III',
                'tax_regime'    => '601',
                'cfdi_use'      => 'G03',
                'address'       => 'Torres Quevedo 890, Fracc. Industrial',
                'city'          => 'Ciudad Juárez',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'zip_code'      => '32310',
                'credit_limit'  => 80000,
                'payment_terms' => 15,
                'status'        => 'active',
            ],
            [
                'name'          => 'Planta Industrial Chihuahua S.A. de C.V.',
                'rfc'           => 'PIC981105JJJ',
                'tax_regime'    => '601',
                'cfdi_use'      => 'G01',
                'address'       => 'Blvd. Manuel Gómez Morín 3111',
                'city'          => 'Chihuahua',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'zip_code'      => '31125',
                'credit_limit'  => 300000,
                'payment_terms' => 30,
                'status'        => 'active',
                'description'   => 'Planta de manufactura automotriz. Requiere materiales eléctricos certificados.',
            ],
            [
                'name'          => 'Agropecuaria San Marcos S.A. de C.V.',
                'rfc'           => 'ASM020314KKK',
                'tax_regime'    => '601',
                'cfdi_use'      => 'G03',
                'address'       => 'Carretera Delicias–Camargo Km 8',
                'city'          => 'Delicias',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'zip_code'      => '33000',
                'credit_limit'  => 50000,
                'payment_terms' => 0,
                'status'        => 'active',
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['company_id' => $company->id, 'rfc' => $data['rfc']],
                array_merge($data, [
                    'company_id'  => $company->id,
                    'assigned_to' => $vendedor?->id,
                ])
            );
        }
    }
}
