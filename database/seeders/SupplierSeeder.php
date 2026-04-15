<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        $suppliers = [
            [
                'name'          => 'Distribuidora Eléctrica del Norte S.A. de C.V.',
                'internal_code' => 'PROV-001',
                'rfc'           => 'DEN010101AAA',
                'type'          => 'company',
                'address'       => 'Blvd. Díaz Ordaz 890',
                'city'          => 'Chihuahua',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'payment_terms' => 30,
                'status'        => 'active',
                'description'   => 'Proveedor principal de materiales eléctricos.',
            ],
            [
                'name'          => 'Herramientas Industriales del Pacífico S.A.',
                'internal_code' => 'PROV-002',
                'rfc'           => 'HIP920315BBB',
                'type'          => 'company',
                'address'       => 'Parque Industrial Moctezuma 12',
                'city'          => 'Monterrey',
                'state'         => 'Nuevo León',
                'country'       => 'México',
                'payment_terms' => 15,
                'status'        => 'active',
            ],
            [
                'name'          => 'EPP México S.A. de C.V.',
                'internal_code' => 'PROV-003',
                'rfc'           => 'EPM050601CCC',
                'type'          => 'company',
                'address'       => 'Av. Insurgentes Sur 4523',
                'city'          => 'Ciudad de México',
                'state'         => 'CDMX',
                'country'       => 'México',
                'payment_terms' => 30,
                'status'        => 'active',
                'description'   => 'Equipo de protección personal certificado NOM.',
            ],
            [
                'name'          => 'Siemens México S.A. de C.V.',
                'internal_code' => 'PROV-004',
                'rfc'           => 'SMX980101DDD',
                'type'          => 'company',
                'address'       => 'Lago Zurich 245',
                'city'          => 'Ciudad de México',
                'state'         => 'CDMX',
                'country'       => 'México',
                'payment_terms' => 45,
                'status'        => 'active',
            ],
            [
                'name'          => 'Materiales y Suministros del Noreste S.A.',
                'internal_code' => 'PROV-005',
                'rfc'           => 'MSN110201EEE',
                'type'          => 'company',
                'address'       => 'Calle Tecnológico 234',
                'city'          => 'Delicias',
                'state'         => 'Chihuahua',
                'country'       => 'México',
                'payment_terms' => 0,
                'status'        => 'active',
                'description'   => 'Proveedor local, entrega inmediata.',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::firstOrCreate(
                ['company_id' => $company->id, 'rfc' => $data['rfc']],
                array_merge($data, ['company_id' => $company->id])
            );
        }
    }
}
