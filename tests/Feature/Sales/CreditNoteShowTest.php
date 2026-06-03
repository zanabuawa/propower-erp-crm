<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleCreditNote;
use App\Models\SaleInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreditNoteShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::create([
            'name' => 'Test Co', 'legal_name' => 'Test Co S.A.', 'rfc' => 'ABC123456789',
        ]);
        $this->user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'company_id' => $this->company->id,
        ]);
        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name'       => 'Cliente Test S.A.',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function apply_changes_note_status_to_applied(): void
    {
        $invoice = SaleInvoice::create([
            'company_id'  => $this->company->id,
            'customer_id' => $this->customer->id,
            'created_by'  => $this->user->id,
            'folio'       => 'FAC-0001',
            'currency'    => 'MXN',
            'status'      => 'sent',
            'total'       => 10000,
            'paid_amount' => 0,
        ]);
        $note = SaleCreditNote::create([
            'company_id'      => $this->company->id,
            'sale_invoice_id' => $invoice->id,
            'customer_id'     => $this->customer->id,
            'created_by'      => $this->user->id,
            'folio'           => 'NC-0001',
            'currency'        => 'MXN',
            'status'          => 'draft',
            'reason'          => 'Devolución parcial de mercancía',
            'total'           => 500,
        ]);

        Livewire::test('App\Livewire\Sales\CreditNoteShow', ['creditNote' => $note])
            ->call('apply');

        $this->assertEquals('applied', $note->fresh()->status);
    }

    /** @test */
    public function cancel_changes_note_status_to_cancelled(): void
    {
        $invoice = SaleInvoice::create([
            'company_id'  => $this->company->id,
            'customer_id' => $this->customer->id,
            'created_by'  => $this->user->id,
            'folio'       => 'FAC-0002',
            'currency'    => 'MXN',
            'status'      => 'sent',
            'total'       => 5000,
            'paid_amount' => 0,
        ]);
        $note = SaleCreditNote::create([
            'company_id'      => $this->company->id,
            'sale_invoice_id' => $invoice->id,
            'customer_id'     => $this->customer->id,
            'created_by'      => $this->user->id,
            'folio'           => 'NC-0002',
            'currency'        => 'MXN',
            'status'          => 'draft',
            'reason'          => 'Error en precio',
            'total'           => 200,
        ]);

        Livewire::test('App\Livewire\Sales\CreditNoteShow', ['creditNote' => $note])
            ->call('cancel');

        $this->assertEquals('cancelled', $note->fresh()->status);
    }
}
