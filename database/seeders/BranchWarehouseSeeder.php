<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class BranchWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $matriz  = Branch::where('code', 'MAT')->first();

        // Segunda sucursal
        $norte = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'NTE'],
            [
                'name'      => 'Sucursal Norte',
                'email'     => 'norte@miempresa.com',
                'phone'     => '6141234568',
                'address'   => 'Av. Industrial 456',
                'city'      => 'Chihuahua',
                'state'     => 'Chihuahua',
                'is_active' => true,
            ]
        );

        // Almacenes
        Warehouse::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ALM-MAT'],
            [
                'branch_id'    => $matriz->id,
                'name'         => 'Almacén Matriz',
                'location'     => 'Bodega principal — Planta baja',
                'is_active'    => true,
                'is_defective' => false,
            ]
        );

        Warehouse::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ALM-DEF'],
            [
                'branch_id'    => $matriz->id,
                'name'         => 'Almacén Defectuosos',
                'location'     => 'Bodega trasera — Área de cuarentena',
                'is_active'    => true,
                'is_defective' => true,
            ]
        );

        Warehouse::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ALM-NTE'],
            [
                'branch_id'    => $norte->id,
                'name'         => 'Almacén Sucursal Norte',
                'location'     => 'Bodega norte — Piso 1',
                'is_active'    => true,
                'is_defective' => false,
            ]
        );
    }
}
