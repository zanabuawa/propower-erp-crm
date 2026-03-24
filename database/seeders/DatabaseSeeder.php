<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CompanySeeder::class,
            UserSeeder::class,
        ]);
        // Configuración de compras para la empresa de prueba
        \App\Models\PurchaseSetting::firstOrCreate(
            ['company_id' => \App\Models\Company::first()->id],
            ['currency' => 'MXN', 'level1_amount' => 2000, 'level2_amount' => 10000]
        );
    }
}