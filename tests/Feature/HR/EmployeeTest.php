<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected HrDepartment $department;
    protected HrPosition $position;

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
        $this->department = HrDepartment::create([
            'company_id' => $company->id, 'name' => 'Operaciones', 'is_active' => true,
        ]);
        $this->position = HrPosition::create([
            'company_id'    => $company->id,
            'department_id' => $this->department->id,
            'name'          => 'Operador',
            'salary_type'   => 'monthly',
            'is_active'     => true,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_employee(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', 'Carlos')
            ->set('last_name', 'Ramírez')
            ->set('hire_date', now()->format('Y-m-d'))
            ->set('contract_type', 'indefinido')
            ->set('salary', '15000')
            ->set('salary_period', 'monthly')
            ->set('status', 'active')
            ->set('department_id', $this->department->id)
            ->set('position_id', $this->position->id)
            ->call('save');

        $this->assertDatabaseHas('hr_employees', [
            'first_name'  => 'Carlos',
            'last_name'   => 'Ramírez',
            'company_id'  => $this->user->company_id,
            'status'      => 'active',
        ]);
    }

    /** @test */
    public function first_name_last_name_and_hire_date_are_required(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', '')
            ->set('last_name', '')
            ->set('hire_date', '')
            ->call('save')
            ->assertHasErrors([
                'first_name' => 'required',
                'last_name'  => 'required',
                'hire_date'  => 'required',
            ]);
    }

    /** @test */
    public function contract_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', 'Ana')
            ->set('last_name', 'López')
            ->set('hire_date', now()->format('Y-m-d'))
            ->set('salary', '10000')
            ->set('contract_type', 'freelance')
            ->call('save')
            ->assertHasErrors(['contract_type']);
    }

    /** @test */
    public function salary_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', 'Ana')
            ->set('last_name', 'López')
            ->set('hire_date', now()->format('Y-m-d'))
            ->set('contract_type', 'indefinido')
            ->set('salary', '-5000')
            ->call('save')
            ->assertHasErrors(['salary']);
    }

    /** @test */
    public function clabe_must_be_exactly_18_digits_if_provided(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', 'Ana')
            ->set('last_name', 'López')
            ->set('hire_date', now()->format('Y-m-d'))
            ->set('salary', '10000')
            ->set('contract_type', 'indefinido')
            ->set('clabe', '12345') // demasiado corto
            ->call('save')
            ->assertHasErrors(['clabe']);
    }

    /** @test */
    public function clabe_of_18_digits_is_accepted(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', 'Ana')
            ->set('last_name', 'López')
            ->set('hire_date', now()->format('Y-m-d'))
            ->set('salary', '10000')
            ->set('contract_type', 'indefinido')
            ->set('salary_period', 'monthly')
            ->set('status', 'active')
            ->set('clabe', '123456789012345678')
            ->call('save')
            ->assertHasNoErrors(['clabe']);
    }

    /** @test */
    public function email_must_be_valid_if_provided(): void
    {
        Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('first_name', 'Ana')
            ->set('last_name', 'López')
            ->set('hire_date', now()->format('Y-m-d'))
            ->set('salary', '10000')
            ->set('contract_type', 'indefinido')
            ->set('email', 'no-valido')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    /** @test */
    public function positions_load_when_department_changes(): void
    {
        $component = Livewire::test('App\Livewire\HR\EmployeeForm')
            ->set('department_id', $this->department->id);

        $positions = $component->get('positions');
        $this->assertNotEmpty($positions);
        $this->assertEquals($this->position->name, $positions[0]['name']);
    }

    /** @test */
    public function can_update_employee(): void
    {
        $employee = HrEmployee::create([
            'company_id'    => $this->user->company_id,
            'first_name'    => 'Pedro',
            'last_name'     => 'Sánchez',
            'hire_date'     => now(),
            'contract_type' => 'indefinido',
            'salary'        => 12000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);

        Livewire::test('App\Livewire\HR\EmployeeForm', ['employee' => $employee])
            ->set('first_name', 'Pedro José')
            ->set('salary', '18000')
            ->call('save');

        $this->assertEquals('Pedro José', $employee->fresh()->first_name);
        $this->assertEquals(18000, $employee->fresh()->salary);
    }
}
