<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseCategoryUnitTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

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
        $this->branch = Branch::create([
            'company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    // ── Warehouse ─────────────────────────────────────────────────────────────

    /** @test */
    public function can_create_warehouse(): void
    {
        Livewire::test('App\Livewire\Inventory\WarehouseForm')
            ->set('branch_id', $this->branch->id)
            ->set('name', 'Almacén Principal')
            ->set('code', 'ALP')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('warehouses', [
            'name'       => 'Almacén Principal',
            'branch_id'  => $this->branch->id,
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function warehouse_branch_and_name_are_required(): void
    {
        Livewire::test('App\Livewire\Inventory\WarehouseForm')
            ->set('branch_id', null)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['branch_id' => 'required', 'name' => 'required']);
    }

    /** @test */
    public function can_update_warehouse(): void
    {
        $wh = Warehouse::create([
            'company_id'   => $this->user->company_id,
            'branch_id'    => $this->branch->id,
            'name'         => 'Almacén Viejo',
            'is_active'    => true,
            'is_defective' => false,
        ]);

        Livewire::test('App\Livewire\Inventory\WarehouseForm', ['warehouse' => $wh])
            ->set('name', 'Almacén Nuevo')
            ->set('is_defective', true)
            ->call('save');

        $this->assertEquals('Almacén Nuevo', $wh->fresh()->name);
        $this->assertTrue($wh->fresh()->is_defective);
    }

    // ── Category ──────────────────────────────────────────────────────────────

    /** @test */
    public function can_create_category(): void
    {
        Livewire::test('App\Livewire\Inventory\CategoryForm')
            ->set('name', 'Herramientas')
            ->set('color', '#ff5733')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('categories', [
            'name'       => 'Herramientas',
            'color'      => '#ff5733',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function category_name_and_color_are_required(): void
    {
        Livewire::test('App\Livewire\Inventory\CategoryForm')
            ->set('name', '')
            ->set('color', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'color' => 'required']);
    }

    /** @test */
    public function can_create_subcategory_under_parent(): void
    {
        $parent = Category::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Electrónica',
            'slug'       => 'electronica',
            'color'      => '#000000',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Inventory\CategoryForm')
            ->set('name', 'Cables')
            ->set('color', '#333333')
            ->set('parent_id', $parent->id)
            ->call('save');

        $this->assertDatabaseHas('categories', [
            'name'      => 'Cables',
            'parent_id' => $parent->id,
        ]);
    }

    /** @test */
    public function can_update_category(): void
    {
        $cat = Category::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Categoría Original',
            'slug'       => 'categoria-original',
            'color'      => '#ffffff',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Inventory\CategoryForm', ['category' => $cat])
            ->set('name', 'Categoría Actualizada')
            ->set('is_active', false)
            ->call('save');

        $this->assertEquals('Categoría Actualizada', $cat->fresh()->name);
        $this->assertFalse($cat->fresh()->is_active);
    }

    // ── Unit of Measure ───────────────────────────────────────────────────────

    /** @test */
    public function can_create_unit_of_measure(): void
    {
        Livewire::test('App\Livewire\Inventory\UnitForm')
            ->set('name', 'Kilogramo')
            ->set('abbreviation', 'kg')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('unit_of_measures', [
            'name'         => 'Kilogramo',
            'abbreviation' => 'kg',
            'company_id'   => $this->user->company_id,
        ]);
    }

    /** @test */
    public function unit_name_and_abbreviation_are_required(): void
    {
        Livewire::test('App\Livewire\Inventory\UnitForm')
            ->set('name', '')
            ->set('abbreviation', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'abbreviation' => 'required']);
    }

    /** @test */
    public function can_update_unit_of_measure(): void
    {
        $unit = UnitOfMeasure::create([
            'company_id'   => $this->user->company_id,
            'name'         => 'Pieza',
            'abbreviation' => 'pz',
            'is_active'    => true,
        ]);

        Livewire::test('App\Livewire\Inventory\UnitForm', ['unitOfMeasure' => $unit])
            ->set('name', 'Pieza (unidad)')
            ->set('abbreviation', 'pza')
            ->call('save');

        $this->assertEquals('Pieza (unidad)', $unit->fresh()->name);
        $this->assertEquals('pza', $unit->fresh()->abbreviation);
    }
}
