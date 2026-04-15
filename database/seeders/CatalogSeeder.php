<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        // ── Unidades de medida (claves SAT c_ClaveUnidad) ────────────────────
        $units = [
            ['name' => 'Pieza',          'abbreviation' => 'H87'],
            ['name' => 'Metro',          'abbreviation' => 'MTR'],
            ['name' => 'Rollo',          'abbreviation' => 'XRO'],
            ['name' => 'Caja',           'abbreviation' => 'XBX'],
            ['name' => 'Kilogramo',      'abbreviation' => 'KGM'],
            ['name' => 'Litro',          'abbreviation' => 'LTR'],
            ['name' => 'Par',            'abbreviation' => 'PR'],
            ['name' => 'Kit / Juego',    'abbreviation' => 'KT'],
            ['name' => 'Hora',           'abbreviation' => 'HUR'],
            ['name' => 'Metro cuadrado', 'abbreviation' => 'MTK'],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::firstOrCreate(
                ['company_id' => $company->id, 'abbreviation' => $unit['abbreviation']],
                ['name' => $unit['name'], 'is_active' => true]
            );
        }

        // ── Categorías ───────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Materiales Eléctricos', 'color' => '#f59e0b', 'children' => [
                'Cables y Conductores',
                'Tableros y Centros de Carga',
                'Protecciones y Fusibles',
                'Conduit y Accesorios',
                'Contactos y Apagadores',
            ]],
            ['name' => 'Herramientas', 'color' => '#3b82f6', 'children' => [
                'Herramienta Manual',
                'Herramienta Eléctrica',
                'Instrumentos de Medición',
            ]],
            ['name' => 'Equipo de Protección Personal', 'color' => '#10b981', 'children' => [
                'Protección de Cabeza',
                'Protección de Manos',
                'Protección Visual',
            ]],
            ['name' => 'Automatización e Instrumentación', 'color' => '#8b5cf6', 'children' => []],
            ['name' => 'Servicios', 'color' => '#6366f1', 'children' => [
                'Instalación',
                'Mantenimiento',
                'Ingeniería',
            ]],
        ];

        foreach ($categories as $catData) {
            $slug   = Str::slug($catData['name']);
            $parent = Category::firstOrCreate(
                ['company_id' => $company->id, 'slug' => $slug],
                [
                    'name'      => $catData['name'],
                    'color'     => $catData['color'],
                    'is_active' => true,
                ]
            );

            foreach ($catData['children'] as $childName) {
                $childSlug = Str::slug($catData['name'] . '-' . $childName);
                Category::firstOrCreate(
                    ['company_id' => $company->id, 'slug' => $childSlug],
                    [
                        'parent_id' => $parent->id,
                        'name'      => $childName,
                        'color'     => $catData['color'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
