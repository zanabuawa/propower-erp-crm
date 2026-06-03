<?php

namespace Tests\Feature\Purchases;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\SupplierCreditNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierCreditNoteShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SupplierCreditNote $note;

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
        $supplier = Supplier::create([
            'company_id' => $company->id,
            'name'       => 'Proveedor Test S.A.',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->note = SupplierCreditNote::create([
            'company_id'  => $company->id,
            'supplier_id' => $supplier->id,
            'created_by'  => $this->user->id,
            'folio'       => 'NCP-000001',
            'currency'    => 'MXN',
            'status'      => 'draft',
            'reason'      => 'return',
            'issued_at'   => now()->toDateString(),
            'total'       => 1500,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function cancel_changes_status_to_cancelled(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteShow', ['creditNote' => $this->note])
            ->call('cancel');

        $this->assertEquals('cancelled', $this->note->fresh()->status);
    }

    /** @test */
    public function open_apply_modal_shows_modal(): void
    {
        Livewire::test('App\Livewire\Purchases\SupplierCreditNoteShow', ['creditNote' => $this->note])
            ->call('openApplyModal')
            ->assertSet('showApplyModal', true);
    }
}
