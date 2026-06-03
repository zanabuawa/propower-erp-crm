<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrPayroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

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
        $this->user->assignRole('admin');
        $this->actingAs($this->user);
    }

    /** @test */
    public function approve_changes_payroll_status_to_approved(): void
    {
        $payroll = HrPayroll::create([
            'company_id'   => $this->company->id,
            'created_by'   => $this->user->id,
            'folio'        => 'NOM-000001',
            'period_type'  => 'monthly',
            'period_start' => now()->startOfMonth(),
            'period_end'   => now()->endOfMonth(),
            'status'       => 'calculated',
        ]);

        Livewire::test('App\Livewire\HR\PayrollShow', ['payroll' => $payroll])
            ->call('approve');

        $this->assertEquals('approved', $payroll->fresh()->status);
    }

    /** @test */
    public function mark_paid_changes_status_to_paid(): void
    {
        $payroll = HrPayroll::create([
            'company_id'   => $this->company->id,
            'created_by'   => $this->user->id,
            'folio'        => 'NOM-000002',
            'period_type'  => 'monthly',
            'period_start' => now()->startOfMonth(),
            'period_end'   => now()->endOfMonth(),
            'status'       => 'approved',
        ]);

        Livewire::test('App\Livewire\HR\PayrollShow', ['payroll' => $payroll])
            ->call('markPaid');

        $this->assertEquals('paid', $payroll->fresh()->status);
    }

    /** @test */
    public function approve_only_works_on_calculated_payrolls(): void
    {
        $payroll = HrPayroll::create([
            'company_id'   => $this->company->id,
            'created_by'   => $this->user->id,
            'folio'        => 'NOM-000003',
            'period_type'  => 'monthly',
            'period_start' => now()->startOfMonth(),
            'period_end'   => now()->endOfMonth(),
            'status'       => 'draft',
        ]);

        Livewire::test('App\Livewire\HR\PayrollShow', ['payroll' => $payroll])
            ->call('approve');

        $this->assertEquals('draft', $payroll->fresh()->status);
    }
}
