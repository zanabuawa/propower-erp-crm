<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\PepsKardex;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $company   = Company::first();
        $almacen   = Warehouse::where('code', 'ALM-MAT')->first();
        $proveedor = Supplier::where('internal_code', 'PROV-001')->first();
        $prov2     = Supplier::where('internal_code', 'PROV-002')->first();
        $prov3     = Supplier::where('internal_code', 'PROV-003')->first();

        $catCables    = Category::where('name', 'Cables y Conductores')->first();
        $catTableros  = Category::where('name', 'Tableros y Centros de Carga')->first();
        $catProtec    = Category::where('name', 'Protecciones y Fusibles')->first();
        $catConduit   = Category::where('name', 'Conduit y Accesorios')->first();
        $catContactos = Category::where('name', 'Contactos y Apagadores')->first();
        $catManual    = Category::where('name', 'Herramienta Manual')->first();
        $catMedicion  = Category::where('name', 'Instrumentos de Medición')->first();
        $catCabeza    = Category::where('name', 'Protección de Cabeza')->first();
        $catManos     = Category::where('name', 'Protección de Manos')->first();
        $catServ      = Category::where('name', 'Instalación')->first();

        $pza   = UnitOfMeasure::where('abbreviation', 'H87')->first();
        $mt    = UnitOfMeasure::where('abbreviation', 'MTR')->first();
        $rollo = UnitOfMeasure::where('abbreviation', 'XRO')->first();
        $par   = UnitOfMeasure::where('abbreviation', 'PR')->first();
        $hr    = UnitOfMeasure::where('abbreviation', 'HUR')->first();

        $products = [
            // Cables
            ['sku' => 'CAB-THW-12', 'name' => 'Cable THW Cal. 12 AWG', 'sat_product_code' => '27111600', 'sat_unit_code' => 'MTR',
             'category_id' => $catCables?->id, 'unit_id' => $mt, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 8.50, 'profit_margin' => 30, 'sale_price' => 11.05, 'min_stock' => 500, 'stock' => 2000],

            ['sku' => 'CAB-THW-10', 'name' => 'Cable THW Cal. 10 AWG', 'sat_product_code' => '27111600', 'sat_unit_code' => 'MTR',
             'category_id' => $catCables?->id, 'unit_id' => $mt, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 12.50, 'profit_margin' => 28, 'sale_price' => 16.00, 'min_stock' => 300, 'stock' => 1200],

            ['sku' => 'CAB-THHW-8', 'name' => 'Cable THHW-LS Cal. 8 AWG', 'sat_product_code' => '27111600', 'sat_unit_code' => 'MTR',
             'category_id' => $catCables?->id, 'unit_id' => $mt, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 28.00, 'profit_margin' => 25, 'sale_price' => 35.00, 'min_stock' => 100, 'stock' => 500],

            ['sku' => 'CAB-CTRL-RD', 'name' => 'Cable de Control Rojo Cal. 14 AWG', 'sat_product_code' => '27111600', 'sat_unit_code' => 'MTR',
             'category_id' => $catCables?->id, 'unit_id' => $rollo, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 320.00, 'profit_margin' => 22, 'sale_price' => 390.40, 'min_stock' => 5, 'stock' => 25,
             'description' => 'Rollo de 100 metros. Cable de control color rojo.'],

            // Tableros
            ['sku' => 'TAB-20C', 'name' => 'Centro de Carga 20 Circuitos Monofásico', 'sat_product_code' => '39121500', 'sat_unit_code' => 'H87',
             'category_id' => $catTableros?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 850.00, 'profit_margin' => 35, 'sale_price' => 1147.50, 'min_stock' => 5, 'stock' => 20],

            ['sku' => 'TAB-12C', 'name' => 'Centro de Carga 12 Circuitos Monofásico', 'sat_product_code' => '39121500', 'sat_unit_code' => 'H87',
             'category_id' => $catTableros?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 580.00, 'profit_margin' => 35, 'sale_price' => 783.00, 'min_stock' => 5, 'stock' => 15],

            // Protecciones
            ['sku' => 'INT-1P-15A', 'name' => 'Interruptor Termomagnético 1P 15A', 'sat_product_code' => '39121500', 'sat_unit_code' => 'H87',
             'category_id' => $catProtec?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 85.00, 'profit_margin' => 40, 'sale_price' => 119.00, 'min_stock' => 20, 'stock' => 80],

            ['sku' => 'INT-1P-20A', 'name' => 'Interruptor Termomagnético 1P 20A', 'sat_product_code' => '39121500', 'sat_unit_code' => 'H87',
             'category_id' => $catProtec?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 95.00, 'profit_margin' => 40, 'sale_price' => 133.00, 'min_stock' => 20, 'stock' => 60],

            ['sku' => 'INT-2P-60A', 'name' => 'Interruptor Termomagnético 2P 60A', 'sat_product_code' => '39121500', 'sat_unit_code' => 'H87',
             'category_id' => $catProtec?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 420.00, 'profit_margin' => 35, 'sale_price' => 567.00, 'min_stock' => 5, 'stock' => 18],

            // Conduit
            ['sku' => 'CON-EMT-34', 'name' => 'Conduit EMT 3/4"', 'sat_product_code' => '40141700', 'sat_unit_code' => 'MTR',
             'category_id' => $catConduit?->id, 'unit_id' => $mt, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 18.00, 'profit_margin' => 30, 'sale_price' => 23.40, 'min_stock' => 200, 'stock' => 800],

            ['sku' => 'CON-RGD-1', 'name' => 'Conduit Rígido 1"', 'sat_product_code' => '40141700', 'sat_unit_code' => 'MTR',
             'category_id' => $catConduit?->id, 'unit_id' => $mt, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 35.00, 'profit_margin' => 28, 'sale_price' => 44.80, 'min_stock' => 100, 'stock' => 400],

            // Contactos
            ['sku' => 'CON-2P-T', 'name' => 'Contacto Doble Polarizado con Tierra', 'sat_product_code' => '39121414', 'sat_unit_code' => 'H87',
             'category_id' => $catContactos?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 45.00, 'profit_margin' => 45, 'sale_price' => 65.25, 'min_stock' => 50, 'stock' => 200],

            ['sku' => 'APA-SEN', 'name' => 'Apagador Sencillo', 'sat_product_code' => '39121414', 'sat_unit_code' => 'H87',
             'category_id' => $catContactos?->id, 'unit_id' => $pza, 'supplier_id' => $proveedor?->id,
             'purchase_price' => 35.00, 'profit_margin' => 45, 'sale_price' => 50.75, 'min_stock' => 50, 'stock' => 150],

            // Herramientas
            ['sku' => 'HER-DEST-PH8', 'name' => 'Destornillador Phillips 8"', 'sat_product_code' => '27111900', 'sat_unit_code' => 'H87',
             'category_id' => $catManual?->id, 'unit_id' => $pza, 'supplier_id' => $prov2?->id,
             'purchase_price' => 85.00, 'profit_margin' => 50, 'sale_price' => 127.50, 'min_stock' => 10, 'stock' => 30],

            ['sku' => 'HER-DEST-PL8', 'name' => 'Destornillador Plano 8"', 'sat_product_code' => '27111900', 'sat_unit_code' => 'H87',
             'category_id' => $catManual?->id, 'unit_id' => $pza, 'supplier_id' => $prov2?->id,
             'purchase_price' => 75.00, 'profit_margin' => 50, 'sale_price' => 112.50, 'min_stock' => 10, 'stock' => 25],

            ['sku' => 'MED-MULT-DIG', 'name' => 'Multímetro Digital', 'sat_product_code' => '41112201', 'sat_unit_code' => 'H87',
             'category_id' => $catMedicion?->id, 'unit_id' => $pza, 'supplier_id' => $prov2?->id,
             'purchase_price' => 650.00, 'profit_margin' => 40, 'sale_price' => 910.00, 'min_stock' => 3, 'stock' => 8],

            // EPP
            ['sku' => 'EPP-CAS-AMAR', 'name' => 'Casco de Seguridad Amarillo Clase E', 'sat_product_code' => '46181601', 'sat_unit_code' => 'H87',
             'category_id' => $catCabeza?->id, 'unit_id' => $pza, 'supplier_id' => $prov3?->id,
             'purchase_price' => 180.00, 'profit_margin' => 40, 'sale_price' => 252.00, 'min_stock' => 10, 'stock' => 35],

            ['sku' => 'EPP-GUANTE-D', 'name' => 'Guantes Dieléctricos Clase 00 (Par)', 'sat_product_code' => '46181608', 'sat_unit_code' => 'PR',
             'category_id' => $catManos?->id, 'unit_id' => $par, 'supplier_id' => $prov3?->id,
             'purchase_price' => 450.00, 'profit_margin' => 35, 'sale_price' => 607.50, 'min_stock' => 5, 'stock' => 15],

            // Servicio
            ['sku' => 'SRV-INST-ELEC', 'name' => 'Servicio de Instalación Eléctrica', 'sat_product_code' => '81101500', 'sat_unit_code' => 'HUR',
             'category_id' => $catServ?->id, 'unit_id' => $hr, 'supplier_id' => null,
             'type' => 'service',
             'purchase_price' => 0, 'profit_margin' => 0, 'sale_price' => 450.00, 'min_stock' => 0, 'stock' => 0],

            ['sku' => 'SRV-MANT-PREV', 'name' => 'Mantenimiento Preventivo Eléctrico', 'sat_product_code' => '72101500', 'sat_unit_code' => 'HUR',
             'category_id' => $catServ?->id, 'unit_id' => $hr, 'supplier_id' => null,
             'type' => 'service',
             'purchase_price' => 0, 'profit_margin' => 0, 'sale_price' => 380.00, 'min_stock' => 0, 'stock' => 0],
        ];

        foreach ($products as $data) {
            $stock = $data['stock'];
            unset($data['stock']);

            $unitId = $data['unit_id'];
            unset($data['unit_id']);

            $product = Product::firstOrCreate(
                ['company_id' => $company->id, 'sku' => $data['sku']],
                array_merge($data, [
                    'company_id'       => $company->id,
                    'unit_of_measure_id' => $unitId?->id,
                    'type'             => $data['type'] ?? 'product',
                    'is_active'        => true,
                    'purchase_price_includes_iva' => false,
                ])
            );

            // Crear stock inicial y lote FIFO (solo productos físicos con stock)
            if ($stock > 0 && $almacen) {
                $stockRecord = Stock::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $almacen->id],
                    ['quantity' => 0]
                );

                if ((float) $stockRecord->quantity === 0.0) {
                    $stockRecord->update(['quantity' => $stock]);

                    $lot = ProductLot::firstOrCreate(
                        ['company_id' => $company->id, 'product_id' => $product->id, 'reference' => 'STOCK-INICIAL'],
                        [
                            'warehouse_id'     => $almacen->id,
                            'lot_number'       => 'LOTE-' . $product->sku . '-001',
                            'initial_quantity' => $stock,
                            'quantity'         => $stock,
                            'unit_cost'        => $product->purchase_price,
                            'entry_date'       => now()->subMonths(2),
                            'status'           => 'active',
                            'notes'            => 'Stock inicial de apertura del sistema',
                        ]
                    );

                    PepsKardex::firstOrCreate(
                        ['company_id' => $company->id, 'lot_id' => $lot->id, 'reference' => 'STOCK-INICIAL'],
                        [
                            'product_id'       => $product->id,
                            'warehouse_id'     => $almacen->id,
                            'movement_type'    => 'purchase',
                            'direction'        => 'in',
                            'quantity'         => $stock,
                            'unit_cost'        => $product->purchase_price,
                            'total_cost'       => $stock * $product->purchase_price,
                            'balance_quantity'  => $stock,
                            'balance_value'    => $stock * $product->purchase_price,
                            'lot_number'       => $lot->lot_number,
                            'moved_at'         => now()->subMonths(2),
                            'notes'            => 'Inventario inicial',
                        ]
                    );
                }
            }
        }
    }
}
