<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveTest extends TestCase
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
            'first_name'    => 'Roberto',
            'last_name'     => 'Martínez',
            'hire_date'     => now()->subYears(3),
            'contract_type' => 'indefinido',
            'salary'        => 18000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_leave(): void
    {
        Livewire::test('App\Livewire\HR\LeaveForm')
            ->set('employee_id', $this->employee->id)
            ->set('type', 'incapacidad_imss')
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2026-06-14')
            ->call('save');

        $this->assertDatabaseHas('hr_leaves', [
            'employee_id' => $this->employee->id,
            'type'        => 'incapacidad_imss',
            'status'      => 'pending',
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\HR\LeaveForm')
            ->set('employee_id', null)
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2026-06-07')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function end_date_must_be_after_or_equal_start_date(): void
    {
        Livewire::test('App\Livewire\HR\LeaveForm')
            ->set('employee_id', $this->employee->id)
            ->set('start_date', '2026-06-15')
            ->set('end_date', '2026-06-10')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\LeaveForm')
            ->set('employee_id', $this->employee->id)
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2026-06-07')
            ->set('type', 'excursion')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function can_update_existing_leave(): void
    {
        $leave = HrLeave::create([
            'company_id'    => $this->user->company_id,
            'employee_id'   => $this->employee->id,
            'type'          => 'permiso_con_goce',
            'start_date'    => '2026-06-01',
            'end_date'      => '2026-06-01',
            'business_days' => 1,
            'status'        => 'pending',
            'created_by'    => $this->user->id,
        ]);

        Livewire::test('App\Livewire\HR\LeaveForm', ['leave' => $leave])
            ->set('end_date', '2026-06-05')
            ->set('notes', 'Permiso extendido por motivos médicos.')
            ->call('save');

        $this->assertEquals('2026-06-05', $leave->fresh()->end_date->format('Y-m-d'));
    }
}
