<?php

namespace Tests\Feature\Purchases;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;

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
        $this->supplier = Supplier::create([
            'company_id' => $company->id,
            'name'       => 'Proveedor Test',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_purchase_order(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('currency', 'MXN')
            ->set('payment_terms', '30')
            ->set('expected_at', now()->addDays(7)->format('Y-m-d'))
            ->set('items', [[
                'product_id'  => null,
                'supplier_id' => $this->supplier->id,
                'description' => 'Cable eléctrico 10mm',
                'quantity'    => 100,
                'unit_price'  => 25.50,
                'tax_rate'    => 16,
                'unit'        => 'm',
            ]])
            ->call('save');

        $this->assertDatabaseHas('purchase_orders', [
            'company_id' => $this->user->company_id,
            'currency'   => 'MXN',
            'status'     => 'draft',
        ]);
    }

    /** @test */
    public function folio_is_generated_with_oc_prefix(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('currency', 'MXN')
            ->set('payment_terms', '0')
            ->set('expected_at', now()->addDays(7)->format('Y-m-d'))
            ->set('items', [[
                'product_id'  => null,
                'supplier_id' => null,
                'description' => 'Tornillos M6',
                'quantity'    => 500,
                'unit_price'  => 0.50,
                'tax_rate'    => 16,
                'unit'        => 'pza',
            ]])
            ->call('save');

        $order = PurchaseOrder::first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('OC-', $order->folio);
    }

    /** @test */
    public function items_description_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('currency', 'MXN')
            ->set('payment_terms', '0')
            ->set('expected_at', now()->addDays(7)->format('Y-m-d'))
            ->set('items', [[
                'product_id'  => null,
                'supplier_id' => null,
                'description' => '',
                'quantity'    => 1,
                'unit_price'  => 100,
                'tax_rate'    => 16,
                'unit'        => '',
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.description']);
    }

    /** @test */
    public function items_quantity_must_be_positive(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('currency', 'MXN')
            ->set('payment_terms', '0')
            ->set('expected_at', now()->addDays(7)->format('Y-m-d'))
            ->set('items', [[
                'product_id'  => null,
                'supplier_id' => null,
                'description' => 'Artículo',
                'quantity'    => 0,
                'unit_price'  => 100,
                'tax_rate'    => 16,
                'unit'        => '',
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);
    }

    /** @test */
    public function currency_must_be_mxn_or_usd(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('currency', 'EUR')
            ->call('save')
            ->assertHasErrors(['currency']);
    }

    /** @test */
    public function expected_at_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('expected_at', '')
            ->call('save')
            ->assertHasErrors(['expected_at' => 'required']);
    }

    /** @test */
    public function subtotal_is_calculated_from_items(): void
    {
        $component = Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('items', [
                [
                    'product_id'  => null,
                    'supplier_id' => null,
                    'description' => 'Item A',
                    'quantity'    => 10,
                    'unit_price'  => 100,
                    'tax_rate'    => 16,
                    'unit'        => '',
                ],
                [
                    'product_id'  => null,
                    'supplier_id' => null,
                    'description' => 'Item B',
                    'quantity'    => 5,
                    'unit_price'  => 200,
                    'tax_rate'    => 16,
                    'unit'        => '',
                ],
            ]);

        $this->assertEquals(2000.0, $component->get('subtotal'));
    }

    /** @test */
    public function tax_is_calculated_from_items(): void
    {
        $component = Livewire::test('App\Livewire\Purchases\OrderForm')
            ->set('items', [[
                'product_id'  => null,
                'supplier_id' => null,
                'description' => 'Item',
                'quantity'    => 1,
                'unit_price'  => 1000,
                'tax_rate'    => 16,
                'unit'        => '',
            ]]);

        $this->assertEquals(160.0, $component->get('tax'));
        $this->assertEquals(1160.0, $component->get('total'));
    }

    /** @test */
    public function can_add_and_remove_items(): void
    {
        $component = Livewire::test('App\Livewire\Purchases\OrderForm');
        $initial   = count($component->get('items'));

        $component->call('addItem');
        $this->assertCount($initial + 1, $component->get('items'));

        $component->call('removeItem', 0);
        $this->assertCount($initial, $component->get('items'));
    }
}
