<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DiscountApproval;
use App\Models\SaleQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DiscountApprovalIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected DiscountApproval $approval;

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
        $quotation = SaleQuotation::create([
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
            'created_by'  => $this->user->id,
            'folio'       => 'COT-0001',
            'currency'    => 'MXN',
            'status'      => 'draft',
        ]);
        $this->approval = DiscountApproval::create([
            'company_id'             => $company->id,
            'requester_id'           => $this->user->id,
            'model_type'             => SaleQuotation::class,
            'model_id'               => $quotation->id,
            'status'                 => 'pending',
            'requested_discount_pct' => 15,
            'max_allowed_pct'        => 10,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function confirm_approve_changes_status_to_approved(): void
    {
        Livewire::test('App\Livewire\Sales\DiscountApprovalIndex')
            ->call('openModal', $this->approval->id, 'approve')
            ->call('confirm');

        $this->assertEquals('approved', $this->approval->fresh()->status);
    }

    /** @test */
    public function confirm_reject_changes_status_to_rejected(): void
    {
        Livewire::test('App\Livewire\Sales\DiscountApprovalIndex')
            ->call('openModal', $this->approval->id, 'reject')
            ->call('confirm');

        $this->assertEquals('rejected', $this->approval->fresh()->status);
    }

    /** @test */
    public function close_modal_resets_approval_id(): void
    {
        Livewire::test('App\Livewire\Sales\DiscountApprovalIndex')
            ->call('openModal', $this->approval->id, 'approve')
            ->assertSet('approvalId', $this->approval->id)
            ->call('closeModal')
            ->assertSet('approvalId', null);
    }
}
