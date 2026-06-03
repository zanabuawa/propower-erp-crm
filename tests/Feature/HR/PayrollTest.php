<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\HrPayroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected HrEmployee $employee;

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
        $this->employee = HrEmployee::create([
            'company_id'    => $company->id,
            'first_name'    => 'Luis',
            'last_name'     => 'García',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 15000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function period_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'trimestral')
            ->set('period_start', '2026-06-01')
            ->set('period_end', '2026-06-30')
            ->call('calculate')
            ->assertHasErrors(['period_type']);
    }

    /** @test */
    public function period_start_is_required(): void
    {
        Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'monthly')
            ->set('period_start', '')
            ->set('period_end', '2026-06-30')
            ->call('calculate')
            ->assertHasErrors(['period_start' => 'required']);
    }

    /** @test */
    public function period_end_must_be_after_or_equal_start(): void
    {
        Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'monthly')
            ->set('period_start', '2026-06-30')
            ->set('period_end', '2026-06-01')
            ->call('calculate')
            ->assertHasErrors(['period_end']);
    }

    /** @test */
    public function calculate_sets_calculated_flag(): void
    {
        $component = Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'monthly')
            ->set('period_start', '2026-06-01')
            ->set('period_end', '2026-06-30')
            ->call('calculate');

        $this->assertTrue($component->get('calculated'));
    }

    /** @test */
    public function save_requires_calculated_items(): void
    {
        Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'weekly')
            ->set('period_start', '2026-06-01')
            ->set('period_end', '2026-06-07')
            ->call('save')
            ->assertHasErrors(['items']);
    }

    /** @test */
    public function can_create_payroll_after_calculate(): void
    {
        Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'monthly')
            ->set('period_start', '2026-06-01')
            ->set('period_end', '2026-06-30')
            ->call('calculate')
            ->call('save');

        $payroll = HrPayroll::where('period_type', 'monthly')->where('status', 'calculated')->first();
        $this->assertNotNull($payroll);
        $this->assertStringStartsWith('NOM-', $payroll->folio);
    }

    /** @test */
    public function period_type_update_recalculates_dates(): void
    {
        $component = Livewire::test('App\Livewire\HR\PayrollForm')
            ->set('period_type', 'biweekly');

        $this->assertNotEmpty($component->get('period_start'));
        $this->assertFalse($component->get('calculated'));
    }
}
