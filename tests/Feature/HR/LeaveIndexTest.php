<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveIndexTest extends TestCase
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
        $this->user->assignRole('admin');

        $this->employee = HrEmployee::create([
            'company_id'    => $company->id,
            'first_name'    => 'Carlos',
            'last_name'     => 'Mendoza',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 15000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function approve_changes_leave_status(): void
    {
        $leave = HrLeave::create([
            'company_id'    => $this->employee->company_id,
            'employee_id'   => $this->employee->id,
            'type'          => 'permiso_con_goce',
            'start_date'    => now()->addDays(5)->toDateString(),
            'end_date'      => now()->addDays(6)->toDateString(),
            'business_days' => 2,
            'reason'        => 'Cita médica',
            'status'        => 'pending',
            'created_by'    => $this->user->id,
        ]);

        Livewire::test('App\Livewire\HR\LeaveIndex')
            ->call('approve', $leave->id);

        $this->assertEquals('approved', $leave->fresh()->status);
    }

    /** @test */
    public function reject_changes_leave_status(): void
    {
        $leave = HrLeave::create([
            'company_id'    => $this->employee->company_id,
            'employee_id'   => $this->employee->id,
            'type'          => 'permiso_sin_goce',
            'start_date'    => now()->addDays(3)->toDateString(),
            'end_date'      => now()->addDays(3)->toDateString(),
            'business_days' => 1,
            'reason'        => 'Asunto personal',
            'status'        => 'pending',
            'created_by'    => $this->user->id,
        ]);

        Livewire::test('App\Livewire\HR\LeaveIndex')
            ->call('reject', $leave->id);

        $this->assertEquals('rejected', $leave->fresh()->status);
    }
}
