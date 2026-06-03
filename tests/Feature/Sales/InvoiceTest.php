<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

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
        $this->customer = Customer::create([
            'company_id'    => $company->id,
            'name'          => 'Cliente Test',
            'status'        => 'active',
            'credit_limit'  => 0,
            'payment_terms' => 0,
        ]);
        $this->actingAs($this->user);
    }

    private function validItem(): array
    {
        return [
            'product_id'       => null,
            'description'      => 'Servicio de energía',
            'quantity'         => 1,
            'unit_price'       => 8000,
            'discount_pct'     => 0,
            'tax_rate'         => 16,
            'unit'             => 'srv',
            'min_sale_price'   => 0,
            'max_discount_pct' => 100,
        ];
    }

    /** @test */
    public function can_create_internal_invoice(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('type', 'internal')
            ->set('currency', 'MXN')
            ->set('payment_method', 'transfer')
            ->set('payment_terms', '0')
            ->set('global_discount', '0')
            ->set('issued_at', now()->format('Y-m-d'))
            ->set('items', [$this->validItem()])
            ->call('save');

        $this->assertDatabaseHas('sale_invoices', [
            'customer_id' => $this->customer->id,
            'type'        => 'internal',
            'company_id'  => $this->user->company_id,
        ]);
    }

    /** @test */
    public function customer_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', null)
            ->call('save')
            ->assertHasErrors(['customer_id' => 'required']);
    }

    /** @test */
    public function type_must_be_internal_or_cfdi(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('type', 'nota_credito')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function payment_method_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('payment_method', 'cheque_viajero')
            ->call('save')
            ->assertHasErrors(['payment_method']);
    }

    /** @test */
    public function issued_at_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('issued_at', '')
            ->call('save')
            ->assertHasErrors(['issued_at' => 'required']);
    }

    /** @test */
    public function items_description_is_required(): void
    {
        $item              = $this->validItem();
        $item['description'] = '';

        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('items', [$item])
            ->call('save')
            ->assertHasErrors(['items.0.description']);
    }

    /** @test */
    public function items_quantity_must_be_positive(): void
    {
        $item             = $this->validItem();
        $item['quantity'] = 0;

        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('items', [$item])
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);
    }

    /** @test */
    public function global_discount_cannot_exceed_100(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('global_discount', '105')
            ->call('save')
            ->assertHasErrors(['global_discount']);
    }

    /** @test */
    public function subtotal_is_calculated_from_items(): void
    {
        $component = Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('items', [
                array_merge($this->validItem(), ['quantity' => 3, 'unit_price' => 2000]),
            ]);

        $this->assertEquals(6000.0, $component->get('subtotal'));
    }

    /** @test */
    public function can_add_and_remove_items(): void
    {
        $component = Livewire::test('App\Livewire\Sales\InvoiceForm');
        $initial   = count($component->get('items'));

        $component->call('addItem');
        $this->assertCount($initial + 1, $component->get('items'));

        $component->call('removeItem', 0);
        $this->assertCount($initial, $component->get('items'));
    }

    /** @test */
    public function folio_is_generated_on_create(): void
    {
        Livewire::test('App\Livewire\Sales\InvoiceForm')
            ->set('customer_id', $this->customer->id)
            ->set('type', 'internal')
            ->set('currency', 'MXN')
            ->set('payment_method', 'cash')
            ->set('payment_terms', '0')
            ->set('global_discount', '0')
            ->set('issued_at', now()->format('Y-m-d'))
            ->set('items', [$this->validItem()])
            ->call('save');

        $invoice = SaleInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertNotNull($invoice->folio);
    }
}
