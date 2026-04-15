<?php

namespace Database\Seeders;

use App\Models\AssetTransfer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $company  = Company::first();
        $matriz   = Branch::where('code', 'MAT')->first();
        $norte    = Branch::where('code', 'NTE')->first();
        $almacen  = Warehouse::where('code', 'ALM-MAT')->first();
        $almNorte = Warehouse::where('code', 'ALM-NTE')->first();
        $gerente  = User::where('email', 'gerente@miempresa.com')->first();
        $empleado = User::where('email', 'empleado@miempresa.com')->first();
        $admin    = User::where('email', 'admin@miempresa.com')->first();

        $assets = [
            [
                'folio'            => 'ACT-000001',
                'name'             => 'Camioneta Nissan NP300 2022',
                'category'         => 'vehiculo',
                'brand'            => 'Nissan',
                'model'            => 'NP300 Frontier 4x2',
                'serial_number'    => '3N6CM0KN7NK123456',
                'description'      => 'Camioneta para traslado de materiales y personal técnico a obra.',
                'acquisition_date' => now()->subYears(2)->subMonths(3)->toDateString(),
                'acquisition_cost' => 285000.00,
                'status'           => 'active',
                'branch_id'        => $matriz?->id,
                'warehouse_id'     => null,
                'assigned_to'      => $empleado?->id,
                'notes'            => 'Verificación vehicular vigente. Seguro con AXA, póliza VH-2024-00782.',
            ],
            [
                'folio'            => 'ACT-000002',
                'name'             => 'Laptop Dell Latitude 5540',
                'category'         => 'computadora',
                'brand'            => 'Dell',
                'model'            => 'Latitude 5540 i7 16GB',
                'serial_number'    => 'DLL5540-987654',
                'description'      => 'Laptop para uso del gerente de proyectos.',
                'acquisition_date' => now()->subYears(1)->toDateString(),
                'acquisition_cost' => 32000.00,
                'status'           => 'active',
                'branch_id'        => $matriz?->id,
                'warehouse_id'     => null,
                'assigned_to'      => $gerente?->id,
                'notes'            => 'Con garantía Dell hasta 2026. Licencia Windows 11 Pro incluida.',
            ],
            [
                'folio'            => 'ACT-000003',
                'name'             => 'Analizador de Redes Fluke 435-II',
                'category'         => 'maquinaria',
                'brand'            => 'Fluke',
                'model'            => '435-II',
                'serial_number'    => 'FLK435-20221105',
                'description'      => 'Analizador de calidad de energía trifásico. Incluye pinzas de corriente y maletín.',
                'acquisition_date' => now()->subYears(2)->toDateString(),
                'acquisition_cost' => 68500.00,
                'status'           => 'active',
                'branch_id'        => $matriz?->id,
                'warehouse_id'     => $almacen?->id,
                'assigned_to'      => $empleado?->id,
                'notes'            => 'Calibración anual requerida. Próxima calibración: noviembre 2026.',
            ],
            [
                'folio'            => 'ACT-000004',
                'name'             => 'Escritorio Ejecutivo + Silla Ergonómica',
                'category'         => 'mobiliario',
                'brand'            => 'Steelcase',
                'model'            => 'Serie Think + Escritorio L',
                'serial_number'    => 'MOB-OF-2023-001',
                'description'      => 'Mobiliario para oficina de gerencia. Juego de escritorio en L más silla ergonómica.',
                'acquisition_date' => now()->subMonths(18)->toDateString(),
                'acquisition_cost' => 12400.00,
                'status'           => 'active',
                'branch_id'        => $matriz?->id,
                'warehouse_id'     => null,
                'assigned_to'      => $gerente?->id,
            ],
            [
                'folio'            => 'ACT-000005',
                'name'             => 'Taladro Percutor Bosch GSB 18V',
                'category'         => 'herramienta',
                'brand'            => 'Bosch',
                'model'            => 'GSB 18V-55',
                'serial_number'    => 'BSC18V-20230567',
                'description'      => 'Taladro percutor inalámbrico 18V con 2 baterías y cargador.',
                'acquisition_date' => now()->subMonths(10)->toDateString(),
                'acquisition_cost' => 4800.00,
                'status'           => 'transferred',
                'branch_id'        => $norte?->id ?? $matriz?->id,
                'warehouse_id'     => $almNorte?->id ?? $almacen?->id,
                'assigned_to'      => null,
                'notes'            => 'Transferido a Sucursal Norte para proyecto de la zona.',
            ],
            [
                'folio'            => 'ACT-000006',
                'name'             => 'Servidor Dell PowerEdge T40',
                'category'         => 'computadora',
                'brand'            => 'Dell',
                'model'            => 'PowerEdge T40',
                'serial_number'    => 'DPE-T40-SRVR-001',
                'description'      => 'Servidor para sistema ERP y respaldos internos.',
                'acquisition_date' => now()->subMonths(6)->toDateString(),
                'acquisition_cost' => 24000.00,
                'status'           => 'active',
                'branch_id'        => $matriz?->id,
                'warehouse_id'     => null,
                'assigned_to'      => $admin?->id,
                'notes'            => 'Alberga la base de datos y aplicación ERP. Respaldo diario a las 2am.',
            ],
        ];

        foreach ($assets as $data) {
            if (! FixedAsset::where('folio', $data['folio'])->exists()) {
                FixedAsset::create(array_merge($data, [
                    'company_id' => $company->id,
                    'is_active'  => true,
                ]));
            }
        }

        // ── Transferencia: Taladro de Matriz a Sucursal Norte ───────────────
        $taladro = FixedAsset::where('folio', 'ACT-000005')->first();
        if ($taladro && ! AssetTransfer::where('folio', 'TRA-AF-000001')->exists()) {
            AssetTransfer::create([
                'company_id'      => $company->id,
                'asset_id'        => $taladro->id,
                'from_branch_id'  => $matriz?->id,
                'to_branch_id'    => $norte?->id ?? $matriz?->id,
                'from_warehouse_id' => $almacen?->id,
                'to_warehouse_id' => $almNorte?->id ?? $almacen?->id,
                'from_user_id'    => $empleado?->id,
                'to_user_id'      => null,
                'requested_by'    => $gerente?->id,
                'folio'           => 'TRA-AF-000001',
                'status'          => 'completed',
                'reason'          => 'Herramienta requerida para proyecto de instalación en Sucursal Norte.',
                'notes'           => 'Transferencia autorizada por gerente. Taladro en buenas condiciones.',
                'transferred_at'  => now()->subDays(8),
            ]);
        }
    }
}
