<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrContract;
use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContractTest extends TestCase
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
            'first_name'    => 'Carlos',
            'last_name'     => 'López',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 20000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_contract(): void
    {
        Livewire::test('App\Livewire\HR\ContractForm')
            ->set('employee_id', $this->employee->id)
            ->set('type', 'indefinido')
            ->set('start_date', '2026-01-01')
            ->set('salary', '20000')
            ->set('work_hours_per_week', 48)
            ->call('save');

        $this->assertDatabaseHas('hr_contracts', [
            'employee_id' => $this->employee->id,
            'type'        => 'indefinido',
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\HR\ContractForm')
            ->set('employee_id', null)
            ->set('type', 'indefinido')
            ->set('start_date', '2026-01-01')
            ->set('salary', '20000')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\ContractForm')
            ->set('employee_id', $this->employee->id)
            ->set('type', 'freelance')
            ->set('start_date', '2026-01-01')
            ->set('salary', '20000')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function salary_is_required(): void
    {
        Livewire::test('App\Livewire\HR\ContractForm')
            ->set('employee_id', $this->employee->id)
            ->set('type', 'indefinido')
            ->set('start_date', '2026-01-01')
            ->set('salary', '')
            ->call('save')
            ->assertHasErrors(['salary' => 'required']);
    }

    /** @test */
    public function end_date_must_be_after_start_date(): void
    {
        Livewire::test('App\Livewire\HR\ContractForm')
            ->set('employee_id', $this->employee->id)
            ->set('type', 'temporal')
            ->set('start_date', '2026-06-15')
            ->set('end_date', '2026-06-10')
            ->set('salary', '15000')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    /** @test */
    public function can_update_existing_contract(): void
    {
        $contract = HrContract::create([
            'company_id'          => $this->user->company_id,
            'employee_id'         => $this->employee->id,
            'created_by'          => $this->user->id,
            'type'                => 'indefinido',
            'start_date'          => '2026-01-01',
            'salary'              => 18000,
            'salary_period'       => 'monthly',
            'work_hours_per_week' => 48,
            'work_days'           => [1, 2, 3, 4, 5],
            'benefits'            => ['aguinaldo_days' => 15, 'vacation_days' => 6, 'vacation_premium_pct' => 25],
            'status'              => 'draft',
        ]);

        Livewire::test('App\Livewire\HR\ContractForm', ['contract' => $contract])
            ->set('salary', '22000')
            ->set('status', 'active')
            ->call('save');

        $this->assertEquals(22000, $contract->fresh()->salary);
    }
}
