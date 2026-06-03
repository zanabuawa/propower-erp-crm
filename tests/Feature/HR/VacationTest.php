<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VacationTest extends TestCase
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
            'first_name'    => 'Juan',
            'last_name'     => 'López',
            'hire_date'     => now()->subYears(2),
            'contract_type' => 'indefinido',
            'salary'        => 15000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_vacation_request(): void
    {
        Livewire::test('App\Livewire\HR\VacationForm')
            ->set('employee_id', $this->employee->id)
            ->set('start_date', '2026-07-01')
            ->set('end_date', '2026-07-11')
            ->call('save');

        $this->assertDatabaseHas('hr_leaves', [
            'employee_id' => $this->employee->id,
            'type'        => 'vacaciones',
            'status'      => 'pending',
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\HR\VacationForm')
            ->set('employee_id', null)
            ->set('start_date', '2026-07-01')
            ->set('end_date', '2026-07-11')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function end_date_must_be_after_or_equal_start_date(): void
    {
        Livewire::test('App\Livewire\HR\VacationForm')
            ->set('employee_id', $this->employee->id)
            ->set('start_date', '2026-07-15')
            ->set('end_date', '2026-07-10')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    /** @test */
    public function business_days_are_calculated(): void
    {
        $component = Livewire::test('App\Livewire\HR\VacationForm')
            ->set('employee_id', $this->employee->id)
            ->set('start_date', '2026-07-06')
            ->set('end_date', '2026-07-10');

        // Mon-Fri = 5 business days
        $this->assertEquals(5, $component->get('businessDays'));
    }

    /** @test */
    public function can_update_existing_vacation(): void
    {
        $leave = HrLeave::create([
            'company_id'    => $this->user->company_id,
            'employee_id'   => $this->employee->id,
            'type'          => 'vacaciones',
            'start_date'    => '2026-07-01',
            'end_date'      => '2026-07-05',
            'business_days' => 3,
            'status'        => 'pending',
            'created_by'    => $this->user->id,
        ]);

        Livewire::test('App\Livewire\HR\VacationForm', ['leave' => $leave])
            ->set('start_date', '2026-08-03')
            ->set('end_date', '2026-08-07')
            ->call('save');

        $this->assertEquals('2026-08-03', $leave->fresh()->start_date->format('Y-m-d'));
    }
}
