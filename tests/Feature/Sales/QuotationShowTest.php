<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SaleQuotation $quotation;

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
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Cliente Test']);
        $this->quotation = SaleQuotation::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'created_by' => $this->user->id,
            'folio'      => 'COT-0001',
            'currency'   => 'MXN',
            'status'     => 'draft',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function mark_as_sent_changes_status(): void
    {
        Livewire::test('App\Livewire\Sales\QuotationShow', ['quotation' => $this->quotation])
            ->call('markAsSent');

        $this->assertEquals('sent', $this->quotation->fresh()->status);
    }

    /** @test */
    public function accept_changes_status_to_accepted(): void
    {
        Livewire::test('App\Livewire\Sales\QuotationShow', ['quotation' => $this->quotation])
            ->call('accept');

        $this->assertEquals('accepted', $this->quotation->fresh()->status);
    }

    /** @test */
    public function reject_changes_status_to_rejected(): void
    {
        Livewire::test('App\Livewire\Sales\QuotationShow', ['quotation' => $this->quotation])
            ->call('reject');

        $this->assertEquals('rejected', $this->quotation->fresh()->status);
    }
}
