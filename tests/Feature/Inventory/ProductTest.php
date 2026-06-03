<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $company = Company::create([
            'name'       => 'Test Company',
            'legal_name' => 'Test Company S.A.',
            'rfc'        => 'ABC123456789',
        ]);

        $this->user = User::create([
            'name'       => 'Admin',
            'email'      => 'admin@example.com',
            'password'   => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_product(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('type', 'product')
            ->set('name', 'Cable HDMI 3m')
            ->set('purchase_price', '150')
            ->set('profit_margin', '40')
            ->set('min_stock', '5')
            ->set('max_stock', '100')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('products', [
            'name'       => 'Cable HDMI 3m',
            'type'       => 'product',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function can_create_service(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('type', 'service')
            ->set('name', 'Instalación eléctrica')
            ->set('purchase_price', '0')
            ->set('profit_margin', '50')
            ->set('min_stock', '0')
            ->set('max_stock', '0')
            ->call('save');

        $this->assertDatabaseHas('products', [
            'name' => 'Instalación eléctrica',
            'type' => 'service',
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function type_must_be_product_or_service(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('name', 'Test')
            ->set('type', 'invalid')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function profit_margin_below_10_is_auto_corrected_to_10(): void
    {
        // El hook updatedProfitMargin() corrige el valor en lugar de lanzar error
        $component = Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('profit_margin', '5');

        $this->assertEquals('10', $component->get('profit_margin'));
    }

    /** @test */
    public function sale_price_is_calculated_from_purchase_price_and_margin(): void
    {
        // Con precio $1000 y margen 30%: precio_venta = 1000 / (1 - 0.30) = 1428.57
        $component = Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('purchase_price', '1000')
            ->set('profit_margin', '30');

        $this->assertEqualsWithDelta(1428.57, $component->get('normalSalePrice'), 0.5);
    }

    /** @test */
    public function can_update_existing_product(): void
    {
        $product = Product::create([
            'company_id'     => $this->user->company_id,
            'type'           => 'product',
            'name'           => 'Producto Viejo',
            'purchase_price' => '100',
            'profit_margin'  => '30',
            'sale_price'     => '142.86',
            'min_stock'      => '0',
            'max_stock'      => '0',
            'is_active'      => true,
        ]);

        Livewire::test('App\Livewire\Inventory\ProductForm', ['product' => $product])
            ->set('name', 'Producto Actualizado')
            ->set('purchase_price', '200')
            ->set('profit_margin', '40')
            ->call('save');

        $this->assertEquals('Producto Actualizado', $product->fresh()->name);
        $this->assertEquals('200.00', $product->fresh()->purchase_price);
    }

    /** @test */
    public function can_create_inline_category(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('newCategoryName', 'Electrónica')
            ->set('newCategoryColor', '#ff0000')
            ->call('saveCategory');

        $this->assertDatabaseHas('categories', [
            'name'       => 'Electrónica',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function inline_category_requires_name(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('newCategoryName', '')
            ->call('saveCategory')
            ->assertHasErrors(['newCategoryName' => 'required']);
    }

    /** @test */
    public function can_create_inline_supplier(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('newSupplierName', 'Proveedor Rápido S.A.')
            ->call('saveSupplier');

        $this->assertDatabaseHas('suppliers', [
            'name'       => 'Proveedor Rápido S.A.',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function profit_margin_cannot_exceed_999(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('name', 'Producto')
            ->set('purchase_price', '100')
            ->set('profit_margin', '1000')
            ->call('save')
            ->assertHasErrors(['profit_margin']);
    }
}
