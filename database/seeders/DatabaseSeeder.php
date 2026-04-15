<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Geodatos (países, estados, ciudades) ─────────────────────────
            WorldSeeder::class,

            // ── Infraestructura base ─────────────────────────────────────────
            RolesAndPermissionsSeeder::class,
            CompanySeeder::class,
            BranchWarehouseSeeder::class,
            UserSeeder::class,

            // ── Catálogos ────────────────────────────────────────────────────
            CatalogSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,

            // ── Inventario y finanzas ────────────────────────────────────────
            ProductSeeder::class,
            FinanceSeeder::class,

            // ── Operaciones ──────────────────────────────────────────────────
            PurchaseSeeder::class,
            SalesSeeder::class,

            // ── Otros módulos ────────────────────────────────────────────────
            ProjectSeeder::class,
            AssetSeeder::class,
        ]);
    }
}
