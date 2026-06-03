<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $company = Company::create([
            'name'       => 'Test Company',
            'legal_name' => 'Test Company S.A.',
            'rfc'        => 'ABC123456789',
        ]);

        $this->user = User::create([
            'name'       => 'Admin User',
            'email'      => 'admin@example.com',
            'password'   => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_department(): void
    {
        Livewire::test('App\Livewire\HR\DepartmentForm')
            ->set('name', 'Tecnología')
            ->set('code', 'TEC')
            ->set('description', 'Departamento de TI')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('hr_departments', [
            'name'       => 'Tecnología',
            'code'       => 'TEC',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\HR\DepartmentForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function name_max_length_is_enforced(): void
    {
        Livewire::test('App\Livewire\HR\DepartmentForm')
            ->set('name', str_repeat('A', 101))
            ->call('save')
            ->assertHasErrors(['name' => 'max']);
    }

    /** @test */
    public function can_update_existing_department(): void
    {
        $dept = HrDepartment::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Recursos Humanos',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\HR\DepartmentForm', ['department' => $dept])
            ->set('name', 'RRHH')
            ->set('code', 'RRHH')
            ->call('save');

        $this->assertEquals('RRHH', $dept->fresh()->name);
        $this->assertEquals('RRHH', $dept->fresh()->code);
    }

    /** @test */
    public function can_deactivate_department(): void
    {
        $dept = HrDepartment::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Ventas',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\HR\DepartmentForm', ['department' => $dept])
            ->set('is_active', false)
            ->call('save');

        $this->assertFalse($dept->fresh()->is_active);
    }
}
