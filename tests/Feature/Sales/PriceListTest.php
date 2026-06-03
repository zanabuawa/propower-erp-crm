<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PriceListTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_price_list_without_items(): void
    {
        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', 'Lista Distribuidores')
            ->set('currency', 'MXN')
            ->set('is_active', true)
            ->set('items', [])
            ->call('save');

        $this->assertDatabaseHas('price_lists', [
            'name'       => 'Lista Distribuidores',
            'currency'   => 'MXN',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function currency_must_be_mxn_or_usd(): void
    {
        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', 'Lista Precios')
            ->set('currency', 'EUR')
            ->call('save')
            ->assertHasErrors(['currency']);
    }

    /** @test */
    public function valid_to_must_be_after_or_equal_valid_from(): void
    {
        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', 'Lista Precios')
            ->set('valid_from', '2026-06-01')
            ->set('valid_to', '2026-05-01')
            ->call('save')
            ->assertHasErrors(['valid_to']);
    }

    /** @test */
    public function item_price_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', 'Lista Precios')
            ->set('items', [[
                'id'           => null,
                'product_id'   => null,
                'product_name' => 'Producto',
                'price'        => -100,
                'discount_pct' => 0,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.price']);
    }

    /** @test */
    public function item_discount_cannot_exceed_100(): void
    {
        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', 'Lista Precios')
            ->set('items', [[
                'id'           => null,
                'product_id'   => null,
                'product_name' => 'Producto',
                'price'        => 100,
                'discount_pct' => 110,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.discount_pct']);
    }

    /** @test */
    public function can_update_existing_price_list(): void
    {
        $priceList = PriceList::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Lista Original',
            'currency'   => 'MXN',
            'is_default' => false,
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Sales\PriceListForm', ['priceList' => $priceList])
            ->set('name', 'Lista Actualizada')
            ->set('is_active', false)
            ->call('save');

        $this->assertEquals('Lista Actualizada', $priceList->fresh()->name);
        $this->assertFalse($priceList->fresh()->is_active);
    }

    /** @test */
    public function can_create_price_list_with_product_items(): void
    {
        $product = Product::create([
            'company_id'    => $this->user->company_id,
            'name'          => 'Panel Solar 400W',
            'type'          => 'product',
            'sale_price'    => 5000,
            'purchase_price' => 3000,
            'profit_margin' => 66.67,
            'is_active'     => true,
        ]);

        Livewire::test('App\Livewire\Sales\PriceListForm')
            ->set('name', 'Lista Mayoreo')
            ->set('currency', 'MXN')
            ->set('items', [[
                'id'           => null,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'price'        => 4500,
                'discount_pct' => 10,
            ]])
            ->call('save');

        $priceList = PriceList::where('name', 'Lista Mayoreo')->first();
        $this->assertNotNull($priceList);
        $this->assertCount(1, $priceList->items);
        $this->assertEquals(4500, $priceList->items->first()->price);
    }
}
