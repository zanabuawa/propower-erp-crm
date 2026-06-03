<?php

namespace Tests\Feature\Purchases;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\SupplierCreditNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierCreditNoteTest extends TestCase
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
            'name'       => 'Proveedor Test S.A.',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_supplier_credit_note(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteForm')
            ->set('supplierId', $this->supplier->id)
            ->set('issuedAt', '2026-06-15')
            ->set('reason', 'return')
            ->set('items', [[
                'product_id'  => null,
                'description' => 'Devolución de material defectuoso',
                'quantity'    => '5',
                'unit_price'  => '1200',
                'tax_rate'    => '16',
                'subtotal'    => 6000,
            ]])
            ->call('save');

        $this->assertDatabaseHas('supplier_credit_notes', [
            'supplier_id' => $this->supplier->id,
            'reason'      => 'return',
            'status'      => 'draft',
        ]);
    }

    /** @test */
    public function supplier_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteForm')
            ->set('supplierId', null)
            ->set('issuedAt', '2026-06-15')
            ->set('reason', 'return')
            ->call('save')
            ->assertHasErrors(['supplierId' => 'required']);
    }

    /** @test */
    public function issued_at_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteForm')
            ->set('supplierId', $this->supplier->id)
            ->set('issuedAt', '')
            ->set('reason', 'return')
            ->call('save')
            ->assertHasErrors(['issuedAt' => 'required']);
    }

    /** @test */
    public function reason_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteForm')
            ->set('supplierId', $this->supplier->id)
            ->set('issuedAt', '2026-06-15')
            ->set('reason', 'garantia')
            ->call('save')
            ->assertHasErrors(['reason']);
    }

    /** @test */
    public function item_description_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteForm')
            ->set('supplierId', $this->supplier->id)
            ->set('issuedAt', '2026-06-15')
            ->set('reason', 'error')
            ->set('items', [[
                'product_id'  => null,
                'description' => '',
                'quantity'    => '1',
                'unit_price'  => '500',
                'tax_rate'    => '16',
                'subtotal'    => 500,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.description']);
    }

    /** @test */
    public function folio_is_generated_with_ncp_prefix(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteForm')
            ->set('supplierId', $this->supplier->id)
            ->set('issuedAt', '2026-06-15')
            ->set('reason', 'price_adjustment')
            ->set('items', [[
                'product_id'  => null,
                'description' => 'Ajuste de precio pactado',
                'quantity'    => '10',
                'unit_price'  => '100',
                'tax_rate'    => '16',
                'subtotal'    => 1000,
            ]])
            ->call('save');

        $note = SupplierCreditNote::first();
        $this->assertNotNull($note);
        $this->assertStringStartsWith('NCP-', $note->folio);
    }
}
