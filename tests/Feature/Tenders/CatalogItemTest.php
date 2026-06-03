<?php

namespace Tests\Feature\Tenders;

use App\Models\Company;
use App\Models\TenderCatalogCategory;
use App\Models\TenderCatalogItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogItemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected TenderCatalogCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $company = Company::create([
            'name' => 'Test Co', 'legal_name' => 'Test Co S.A.', 'rfc' => 'ABC123456789',
        ]);
        $this->user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'company_id' => $company->id,
        ]);
        $this->category = TenderCatalogCategory::create([
            'company_id' => $company->id,
            'name'       => 'Instalaciones Eléctricas',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_catalog_item(): void
    {
        Livewire::test('App\Livewire\Tenders\CatalogItemForm')
            ->set('category_id', $this->category->id)
            ->set('name', 'Suministro e instalación de tablero eléctrico 200A')
            ->set('unit', 'PZA')
            ->set('indirect_pct', 10)
            ->set('utility_pct', 5)
            ->set('resources', [[
                'id'          => null,
                'type'        => 'material',
                'description' => 'Tablero eléctrico 200A',
                'unit'        => 'PZA',
                'quantity'    => 1,
                'unit_cost'   => 8000,
            ]])
            ->call('save');

        $this->assertDatabaseHas('tender_catalog_items', [
            'name'        => 'Suministro e instalación de tablero eléctrico 200A',
            'category_id' => $this->category->id,
        ]);
    }

    /** @test */
    public function category_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\CatalogItemForm')
            ->set('category_id', null)
            ->set('name', 'Concepto Sin Categoría')
            ->call('save')
            ->assertHasErrors(['category_id' => 'required']);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\CatalogItemForm')
            ->set('category_id', $this->category->id)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function resource_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Tenders\CatalogItemForm')
            ->set('category_id', $this->category->id)
            ->set('name', 'Concepto con Recurso Inválido')
            ->set('resources', [[
                'id'          => null,
                'type'        => 'herramienta',
                'description' => 'Taladro',
                'unit'        => 'PZA',
                'quantity'    => 1,
                'unit_cost'   => 500,
            ]])
            ->call('save')
            ->assertHasErrors(['resources.0.type']);
    }

    /** @test */
    public function resource_description_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\CatalogItemForm')
            ->set('category_id', $this->category->id)
            ->set('name', 'Concepto Prueba')
            ->set('resources', [[
                'id'          => null,
                'type'        => 'labor',
                'description' => '',
                'unit'        => 'HR',
                'quantity'    => 8,
                'unit_cost'   => 100,
            ]])
            ->call('save')
            ->assertHasErrors(['resources.0.description']);
    }
}
