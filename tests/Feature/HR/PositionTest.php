<?php

namespace Tests\Feature\HR;

use App\Models\Branch;
use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\HrPosition;
use App\Models\HrPositionHeadcount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected HrDepartment $department;

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
            'name'       => 'Admin',
            'email'      => 'admin@example.com',
            'password'   => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->department = HrDepartment::create([
            'company_id' => $company->id,
            'name'       => 'Ingeniería',
            'is_active'  => true,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_position(): void
    {
        Livewire::test('App\Livewire\HR\PositionForm')
            ->set('name', 'Desarrollador Backend')
            ->set('department_id', $this->department->id)
            ->set('salary_type', 'monthly')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('hr_positions', [
            'name'          => 'Desarrollador Backend',
            'department_id' => $this->department->id,
            'salary_type'   => 'monthly',
            'company_id'    => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_and_department_are_required(): void
    {
        Livewire::test('App\Livewire\HR\PositionForm')
            ->set('name', '')
            ->set('department_id', null)
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'department_id' => 'required']);
    }

    /** @test */
    public function salary_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\PositionForm')
            ->set('name', 'Analista')
            ->set('department_id', $this->department->id)
            ->set('salary_type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['salary_type']);
    }

    /** @test */
    public function can_update_position(): void
    {
        $position = HrPosition::create([
            'company_id'    => $this->user->company_id,
            'department_id' => $this->department->id,
            'name'          => 'Analista',
            'salary_type'   => 'monthly',
            'is_active'     => true,
        ]);

        Livewire::test('App\Livewire\HR\PositionForm', ['position' => $position])
            ->set('name', 'Analista Senior')
            ->set('min_salary', '20000')
            ->set('max_salary', '35000')
            ->call('save');

        $this->assertEquals('Analista Senior', $position->fresh()->name);
        $this->assertEquals('20000.00', $position->fresh()->min_salary);
    }

    /** @test */
    public function branch_headcounts_are_saved(): void
    {
        $branch = Branch::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Sucursal Norte',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\HR\PositionForm')
            ->set('name', 'Técnico')
            ->set('department_id', $this->department->id)
            ->set('salary_type', 'monthly')
            ->set("branchHeadcounts.{$branch->id}", 3)
            ->call('save');

        $position = HrPosition::where('name', 'Técnico')->first();
        $this->assertNotNull($position);

        $this->assertDatabaseHas('hr_position_headcounts', [
            'position_id' => $position->id,
            'branch_id'   => $branch->id,
            'headcount'   => 3,
        ]);
    }

    /** @test */
    public function authorized_headcount_equals_sum_of_branches(): void
    {
        $branch1 = Branch::create(['company_id' => $this->user->company_id, 'name' => 'Norte', 'is_active' => true]);
        $branch2 = Branch::create(['company_id' => $this->user->company_id, 'name' => 'Sur', 'is_active' => true]);

        $component = Livewire::test('App\Livewire\HR\PositionForm')
            ->set('name', 'Operador')
            ->set('department_id', $this->department->id)
            ->set('salary_type', 'daily')
            ->set("branchHeadcounts.{$branch1->id}", 2)
            ->set("branchHeadcounts.{$branch2->id}", 3);

        $this->assertEquals(5, $component->get('authorized_headcount'));
    }
}
