<?php

namespace Tests\Feature\HR;

use App\Models\Branch;
use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\HrJobOpening;
use App\Models\HrPosition;
use App\Models\HrPositionHeadcount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobOpeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected HrPosition $position;
    protected Branch $branch;

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

        $dept = HrDepartment::create([
            'company_id' => $company->id,
            'name'       => 'TI',
            'is_active'  => true,
        ]);

        $this->position = HrPosition::create([
            'company_id'    => $company->id,
            'department_id' => $dept->id,
            'name'          => 'Desarrollador',
            'salary_type'   => 'monthly',
            'is_active'     => true,
        ]);

        $this->branch = Branch::create([
            'company_id' => $company->id,
            'name'       => 'Matriz',
            'is_active'  => true,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_job_opening(): void
    {
        Livewire::test('App\Livewire\HR\JobOpeningForm')
            ->set('title', 'Desarrollador PHP Senior')
            ->set('position_id', $this->position->id)
            ->set('type', 'external')
            ->set('quantity', 2)
            ->set('status', 'open')
            ->set('published_at', now()->format('Y-m-d'))
            ->call('save');

        $this->assertDatabaseHas('hr_job_openings', [
            'title'       => 'Desarrollador PHP Senior',
            'position_id' => $this->position->id,
            'quantity'    => 2,
            'type'        => 'external',
            'status'      => 'open',
            'company_id'  => $this->user->company_id,
        ]);
    }

    /** @test */
    public function title_and_position_are_required(): void
    {
        Livewire::test('App\Livewire\HR\JobOpeningForm')
            ->set('title', '')
            ->set('position_id', null)
            ->call('save')
            ->assertHasErrors(['title' => 'required', 'position_id' => 'required']);
    }

    /** @test */
    public function quantity_must_be_at_least_one(): void
    {
        Livewire::test('App\Livewire\HR\JobOpeningForm')
            ->set('title', 'Vacante')
            ->set('position_id', $this->position->id)
            ->set('quantity', 0)
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    /** @test */
    public function closing_date_must_be_after_published_date(): void
    {
        Livewire::test('App\Livewire\HR\JobOpeningForm')
            ->set('title', 'Vacante')
            ->set('position_id', $this->position->id)
            ->set('published_at', '2026-06-01')
            ->set('closing_date', '2026-05-01')
            ->call('save')
            ->assertHasErrors(['closing_date']);
    }

    /** @test */
    public function enforces_headcount_limit_when_slot_info_available(): void
    {
        // Headcount: 2 plazas en esta sucursal
        HrPositionHeadcount::create([
            'company_id'  => $this->user->company_id,
            'position_id' => $this->position->id,
            'branch_id'   => $this->branch->id,
            'headcount'   => 2,
        ]);

        Livewire::test('App\Livewire\HR\JobOpeningForm')
            ->set('title', 'Vacante')
            ->set('position_id', $this->position->id)
            ->set('branch_id', $this->branch->id)
            ->set('quantity', 5)
            ->set('type', 'external')
            ->set('status', 'open')
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    /** @test */
    public function can_update_existing_job_opening(): void
    {
        $opening = HrJobOpening::create([
            'company_id'  => $this->user->company_id,
            'position_id' => $this->position->id,
            'title'       => 'Vacante inicial',
            'type'        => 'internal',
            'quantity'    => 1,
            'status'      => 'open',
            'created_by'  => $this->user->id,
        ]);

        Livewire::test('App\Livewire\HR\JobOpeningForm', ['jobOpening' => $opening])
            ->set('title', 'Vacante actualizada')
            ->set('status', 'paused')
            ->call('save');

        $this->assertEquals('Vacante actualizada', $opening->fresh()->title);
        $this->assertEquals('paused', $opening->fresh()->status);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\JobOpeningForm')
            ->set('title', 'Vacante')
            ->set('position_id', $this->position->id)
            ->set('type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['type']);
    }
}
