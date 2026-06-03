<?php

namespace Tests\Feature\Purchases;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseInvoiceTest extends TestCase
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
            'name'       => 'Proveedor ABC',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_purchase_invoice(): void
    {
        Livewire::test('App\Livewire\Purchases\PurchaseInvoiceForm')
            ->set('supplierId', $this->supplier->id)
            ->set('supplierInvoiceNumber', 'FAC-2026-001')
            ->set('issuedAt', now()->format('Y-m-d'))
            ->set('totalInput', '58000')
            ->set('subtotalInput', '50000')
            ->set('taxInput', '8000')
            ->call('save');

        $this->assertDatabaseHas('purchase_invoices', [
            'supplier_id'             => $this->supplier->id,
            'supplier_invoice_number' => 'FAC-2026-001',
            'company_id'              => $this->user->company_id,
        ]);
    }

    /** @test */
    public function supplier_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\PurchaseInvoiceForm')
            ->set('supplierId', null)
            ->set('supplierInvoiceNumber', 'FAC-001')
            ->set('issuedAt', now()->format('Y-m-d'))
            ->set('totalInput', '1000')
            ->call('save')
            ->assertHasErrors(['supplierId']);
    }

    /** @test */
    public function invoice_number_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\PurchaseInvoiceForm')
            ->set('supplierId', $this->supplier->id)
            ->set('supplierInvoiceNumber', '')
            ->set('issuedAt', now()->format('Y-m-d'))
            ->set('totalInput', '1000')
            ->call('save')
            ->assertHasErrors(['supplierInvoiceNumber']);
    }

    /** @test */
    public function total_must_be_greater_than_zero(): void
    {
        Livewire::test('App\Livewire\Purchases\PurchaseInvoiceForm')
            ->set('supplierId', $this->supplier->id)
            ->set('supplierInvoiceNumber', 'FAC-001')
            ->set('issuedAt', now()->format('Y-m-d'))
            ->set('totalInput', '0')
            ->call('save')
            ->assertHasErrors(['totalInput']);
    }

    /** @test */
    public function issued_at_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\PurchaseInvoiceForm')
            ->set('supplierId', $this->supplier->id)
            ->set('supplierInvoiceNumber', 'FAC-001')
            ->set('issuedAt', '')
            ->set('totalInput', '1000')
            ->call('save')
            ->assertHasErrors(['issuedAt']);
    }

    /** @test */
    public function folio_is_generated_with_fp_prefix(): void
    {
        Livewire::test('App\Livewire\Purchases\PurchaseInvoiceForm')
            ->set('supplierId', $this->supplier->id)
            ->set('supplierInvoiceNumber', 'FAC-2026-002')
            ->set('issuedAt', now()->format('Y-m-d'))
            ->set('totalInput', '11600')
            ->call('save');

        $invoice = \App\Models\PurchaseInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('FP-', $invoice->folio);
    }
}
