<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreditNoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected SaleInvoice $invoice;

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
        $this->invoice = SaleInvoice::create([
            'company_id'    => $company->id,
            'customer_id'   => $this->customer->id,
            'created_by'    => $this->user->id,
            'folio'         => 'FV-000001',
            'status'        => 'stamped',
            'currency'      => 'MXN',
            'subtotal'      => 10000,
            'tax'           => 1600,
            'total'         => 11600,
            'issued_at'     => now(),
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_credit_note(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', $this->customer->id)
            ->set('reason', 'Devolución parcial de mercancía dañada en transporte.')
            ->set('items', [[
                'description' => 'Producto dañado',
                'quantity'    => 2,
                'unit_price'  => 500,
                'tax_rate'    => 16,
                'subtotal'    => 1000,
            ]])
            ->call('save');

        $this->assertDatabaseHas('sale_credit_notes', [
            'sale_invoice_id' => $this->invoice->id,
            'customer_id'     => $this->customer->id,
            'status'          => 'draft',
        ]);
    }

    /** @test */
    public function invoice_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', null)
            ->set('customerId', $this->customer->id)
            ->set('reason', 'Motivo de prueba.')
            ->call('save')
            ->assertHasErrors(['invoiceId' => 'required']);
    }

    /** @test */
    public function customer_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', null)
            ->set('reason', 'Motivo de prueba.')
            ->call('save')
            ->assertHasErrors(['customerId' => 'required']);
    }

    /** @test */
    public function reason_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', $this->customer->id)
            ->set('reason', '')
            ->call('save')
            ->assertHasErrors(['reason' => 'required']);
    }

    /** @test */
    public function items_are_required(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', $this->customer->id)
            ->set('reason', 'Motivo válido.')
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }

    /** @test */
    public function item_description_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', $this->customer->id)
            ->set('reason', 'Motivo válido.')
            ->set('items', [[
                'description' => '',
                'quantity'    => 1,
                'unit_price'  => 100,
                'tax_rate'    => 16,
                'subtotal'    => 100,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.description']);
    }

    /** @test */
    public function item_quantity_must_be_positive(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', $this->customer->id)
            ->set('reason', 'Motivo válido.')
            ->set('items', [[
                'description' => 'Producto',
                'quantity'    => 0,
                'unit_price'  => 100,
                'tax_rate'    => 16,
                'subtotal'    => 0,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);
    }

    /** @test */
    public function folio_is_generated_with_nc_prefix(): void
    {
        Livewire::test('App\Livewire\Sales\CreditNoteForm')
            ->set('invoiceId', $this->invoice->id)
            ->set('customerId', $this->customer->id)
            ->set('reason', 'Devolución total del pedido por calidad.')
            ->set('items', [[
                'description' => 'Servicio',
                'quantity'    => 1,
                'unit_price'  => 200,
                'tax_rate'    => 16,
                'subtotal'    => 200,
            ]])
            ->call('save');

        $note = \App\Models\SaleCreditNote::first();
        $this->assertNotNull($note);
        $this->assertStringStartsWith('NC-', $note->folio);
    }
}
