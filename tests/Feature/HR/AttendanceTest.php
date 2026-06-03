<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceTest extends TestCase
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
            'first_name'    => 'Pedro',
            'last_name'     => 'Ramírez',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 12000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_attendance(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceForm')
            ->set('employee_id', $this->employee->id)
            ->set('date', '2026-06-01')
            ->set('check_in', '08:00')
            ->set('check_out', '17:00')
            ->set('status', 'present')
            ->call('save');

        $this->assertDatabaseHas('hr_attendances', [
            'employee_id' => $this->employee->id,
            'status'      => 'present',
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceForm')
            ->set('employee_id', null)
            ->set('date', '2026-06-01')
            ->set('status', 'present')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function date_is_required(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceForm')
            ->set('employee_id', $this->employee->id)
            ->set('date', '')
            ->set('status', 'present')
            ->call('save')
            ->assertHasErrors(['date' => 'required']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceForm')
            ->set('employee_id', $this->employee->id)
            ->set('date', '2026-06-01')
            ->set('status', 'feriado')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function check_out_must_be_after_check_in(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceForm')
            ->set('employee_id', $this->employee->id)
            ->set('date', '2026-06-01')
            ->set('check_in', '17:00')
            ->set('check_out', '08:00')
            ->set('status', 'present')
            ->call('save')
            ->assertHasErrors(['check_out']);
    }

    /** @test */
    public function can_update_existing_attendance(): void
    {
        $att = HrAttendance::create([
            'company_id'  => $this->user->company_id,
            'employee_id' => $this->employee->id,
            'recorded_by' => $this->user->id,
            'date'        => '2026-06-01',
            'status'      => 'present',
        ]);

        Livewire::test('App\Livewire\HR\AttendanceForm', ['attendance' => $att])
            ->set('check_in', '08:30')
            ->set('check_out', '17:30')
            ->set('notes', 'Entrada con retraso justificado.')
            ->call('save');

        $this->assertStringStartsWith('08:30', $att->fresh()->check_in);
    }
}
