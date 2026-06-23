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
    public function product_sku_and_barcode_are_generated_when_empty(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('type', 'product')
            ->set('name', 'Cable UTP Cat 6')
            ->set('sku', '')
            ->set('barcode', '')
            ->set('purchase_price', '120')
            ->set('profit_margin', '30')
            ->set('min_stock', '5')
            ->set('max_stock', '100')
            ->call('save');

        $product = Product::where('name', 'Cable UTP Cat 6')->first();

        $this->assertNotNull($product);
        $this->assertNotEmpty($product->sku);
        $this->assertNotEmpty($product->barcode);
    }

    /** @test */
    public function product_identifiers_ignore_user_supplied_values_on_create(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('type', 'product')
            ->set('name', 'Interruptor termomagnetico')
            ->set('sku', 'SKU-USUARIO')
            ->set('barcode', 'BARCODE-USUARIO')
            ->set('purchase_price', '250')
            ->set('profit_margin', '30')
            ->set('min_stock', '1')
            ->set('max_stock', '20')
            ->call('save');

        $product = Product::where('name', 'Interruptor termomagnetico')->first();

        $this->assertNotNull($product);
        $this->assertNotSame('SKU-USUARIO', $product->sku);
        $this->assertNotSame('BARCODE-USUARIO', $product->barcode);
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
    public function service_gets_sku_but_not_inventory_barcode(): void
    {
        Livewire::test('App\Livewire\Inventory\ProductForm')
            ->set('type', 'service')
            ->set('name', 'Instalacion de tablero')
            ->set('sku', '')
            ->set('barcode', '')
            ->set('purchase_price', '0')
            ->set('profit_margin', '50')
            ->set('min_stock', '0')
            ->set('max_stock', '0')
            ->call('save');

        $service = Product::where('name', 'Instalacion de tablero')->first();

        $this->assertNotNull($service);
        $this->assertNotEmpty($service->sku);
        $this->assertNull($service->barcode);
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
            'sku'            => 'PRO-00001',
            'barcode'        => '2000010000018',
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
        $this->assertEquals('PRO-00001', $product->fresh()->sku);
        $this->assertEquals('2000010000018', $product->fresh()->barcode);
    }

    /** @test */
    public function product_identifiers_cannot_be_changed_on_update(): void
    {
        $product = Product::create([
            'company_id'     => $this->user->company_id,
            'type'           => 'product',
            'name'           => 'Producto Protegido',
            'sku'            => 'PRO-00002',
            'barcode'        => '2000010000025',
            'purchase_price' => '100',
            'profit_margin'  => '30',
            'sale_price'     => '142.86',
            'min_stock'      => '0',
            'max_stock'      => '0',
            'is_active'      => true,
        ]);

        Livewire::test('App\Livewire\Inventory\ProductForm', ['product' => $product])
            ->set('sku', 'SKU-HACKEADO')
            ->set('barcode', 'BARCODE-HACKEADO')
            ->set('purchase_price', '120')
            ->call('save');

        $product->refresh();

        $this->assertEquals('PRO-00002', $product->sku);
        $this->assertEquals('2000010000025', $product->barcode);
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
