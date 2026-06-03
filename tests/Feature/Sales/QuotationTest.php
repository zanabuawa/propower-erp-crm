<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Product $product;

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

        $this->customer = Customer::create([
            'company_id'    => $company->id,
            'name'          => 'Cliente de Prueba',
            'status'        => 'active',
            'credit_limit'  => 50000,
            'payment_terms' => 30,
        ]);

        $this->product = Product::create([
            'company_id'     => $company->id,
            'type'           => 'product',
            'name'           => 'Producto Test',
            'purchase_price' => '100',
            'profit_margin'  => '40',
            'sale_price'     => '166.67',
            'min_stock'      => '0',
            'max_stock'      => '0',
            'is_active'      => true,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_quotation_with_items(): void
    {
        Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('customer_id', $this->customer->id)
            ->set('currency', 'MXN')
            ->set('valid_days', '30')
            ->set('global_discount', '0')
            ->set('items', [[
                'product_id'       => $this->product->id,
                'description'      => 'Producto Test',
                'quantity'         => 2,
                'unit_price'       => 166.67,
                'discount_pct'     => 0,
                'tax_rate'         => 16,
                'unit'             => 'pz',
                'notes'            => '',
                'min_sale_price'   => 100,
                'max_discount_pct' => 100,
            ]])
            ->call('save');

        $this->assertDatabaseHas('sale_quotations', [
            'customer_id' => $this->customer->id,
            'currency'    => 'MXN',
            'status'      => 'draft',
            'company_id'  => $this->user->company_id,
        ]);

        $quotation = SaleQuotation::first();
        $this->assertNotNull($quotation);
        $this->assertCount(1, $quotation->items);
    }

    /** @test */
    public function customer_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('customer_id', null)
            ->set('items', [[
                'product_id'       => null,
                'description'      => 'Item',
                'quantity'         => 1,
                'unit_price'       => 100,
                'discount_pct'     => 0,
                'tax_rate'         => 16,
                'unit'             => '',
                'notes'            => '',
                'min_sale_price'   => 0,
                'max_discount_pct' => 100,
            ]])
            ->call('save')
            ->assertHasErrors(['customer_id' => 'required']);
    }

    /** @test */
    public function items_require_description_and_positive_quantity(): void
    {
        Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('customer_id', $this->customer->id)
            ->set('items', [[
                'product_id'       => null,
                'description'      => '',
                'quantity'         => 0,
                'unit_price'       => 0,
                'discount_pct'     => 0,
                'tax_rate'         => 16,
                'unit'             => '',
                'notes'            => '',
                'min_sale_price'   => 0,
                'max_discount_pct' => 100,
            ]])
            ->call('save')
            ->assertHasErrors([
                'items.0.description' => 'required',
                'items.0.quantity'    => 'min',
            ]);
    }

    /** @test */
    public function subtotal_is_calculated_correctly(): void
    {
        $component = Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('items', [
                [
                    'product_id'       => null,
                    'description'      => 'A',
                    'quantity'         => 3,
                    'unit_price'       => 100,
                    'discount_pct'     => 0,
                    'tax_rate'         => 16,
                    'unit'             => '',
                    'notes'            => '',
                    'min_sale_price'   => 0,
                    'max_discount_pct' => 100,
                ],
                [
                    'product_id'       => null,
                    'description'      => 'B',
                    'quantity'         => 2,
                    'unit_price'       => 250,
                    'discount_pct'     => 0,
                    'tax_rate'         => 16,
                    'unit'             => '',
                    'notes'            => '',
                    'min_sale_price'   => 0,
                    'max_discount_pct' => 100,
                ],
            ]);

        // 3×100 + 2×250 = 800
        $this->assertEqualsWithDelta(800.0, $component->get('subtotal'), 0.01);
    }

    /** @test */
    public function tax_is_calculated_at_16_percent(): void
    {
        $component = Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('items', [[
                'product_id'       => null,
                'description'      => 'Item',
                'quantity'         => 1,
                'unit_price'       => 1000,
                'discount_pct'     => 0,
                'tax_rate'         => 16,
                'unit'             => '',
                'notes'            => '',
                'min_sale_price'   => 0,
                'max_discount_pct' => 100,
            ]]);

        $this->assertEqualsWithDelta(160.0, $component->get('tax'), 0.01);
    }

    /** @test */
    public function discount_reduces_the_total(): void
    {
        $component = Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('global_discount', '10')
            ->set('items', [[
                'product_id'       => null,
                'description'      => 'Item',
                'quantity'         => 1,
                'unit_price'       => 1000,
                'discount_pct'     => 0,
                'tax_rate'         => 0,
                'unit'             => '',
                'notes'            => '',
                'min_sale_price'   => 0,
                'max_discount_pct' => 100,
            ]]);

        // Subtotal 1000, 10% global = descuento 100, total = 900
        $this->assertEqualsWithDelta(900.0, $component->get('total'), 0.01);
    }

    /** @test */
    public function can_add_and_remove_items(): void
    {
        $component = Livewire::test('App\Livewire\Sales\QuotationForm');

        $initialCount = count($component->get('items'));

        $component->call('addItem');
        $this->assertCount($initialCount + 1, $component->get('items'));

        $component->call('removeItem', 0);
        $this->assertCount($initialCount, $component->get('items'));
    }

    /** @test */
    public function folio_is_generated_sequentially(): void
    {
        $makeItem = fn() => [[
            'product_id'       => null,
            'description'      => 'Item',
            'quantity'         => 1,
            'unit_price'       => 100,
            'discount_pct'     => 0,
            'tax_rate'         => 16,
            'unit'             => '',
            'notes'            => '',
            'min_sale_price'   => 0,
            'max_discount_pct' => 100,
        ]];

        Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('customer_id', $this->customer->id)
            ->set('items', $makeItem())
            ->call('save');

        Livewire::test('App\Livewire\Sales\QuotationForm')
            ->set('customer_id', $this->customer->id)
            ->set('items', $makeItem())
            ->call('save');

        $this->assertEquals('COT-000001', SaleQuotation::first()->folio);
        $this->assertEquals('COT-000002', SaleQuotation::latest('id')->first()->folio);
    }
}
