<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\HrIncident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IncidentTest extends TestCase
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
            'first_name'    => 'María',
            'last_name'     => 'García',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 12000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_incident(): void
    {
        Livewire::test('App\Livewire\HR\IncidentForm')
            ->set('employee_id', $this->employee->id)
            ->set('type', 'tardanza')
            ->set('incident_date', now()->format('Y-m-d'))
            ->set('description', 'El empleado llegó 30 minutos tarde sin justificación.')
            ->set('severity', 'low')
            ->call('save');

        $this->assertDatabaseHas('hr_incidents', [
            'employee_id' => $this->employee->id,
            'type'        => 'tardanza',
            'severity'    => 'low',
            'company_id'  => $this->user->company_id,
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\HR\IncidentForm')
            ->set('employee_id', null)
            ->set('incident_date', now()->format('Y-m-d'))
            ->set('description', 'Descripción detallada de la incidencia.')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function description_requires_at_least_10_chars(): void
    {
        Livewire::test('App\Livewire\HR\IncidentForm')
            ->set('employee_id', $this->employee->id)
            ->set('incident_date', now()->format('Y-m-d'))
            ->set('description', 'Corto')
            ->call('save')
            ->assertHasErrors(['description']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\IncidentForm')
            ->set('employee_id', $this->employee->id)
            ->set('incident_date', now()->format('Y-m-d'))
            ->set('description', 'Descripción detallada de la incidencia.')
            ->set('type', 'accidente_laboral')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function severity_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\IncidentForm')
            ->set('employee_id', $this->employee->id)
            ->set('incident_date', now()->format('Y-m-d'))
            ->set('description', 'Descripción detallada de la incidencia.')
            ->set('severity', 'extremo')
            ->call('save')
            ->assertHasErrors(['severity']);
    }

    /** @test */
    public function incident_date_is_required(): void
    {
        Livewire::test('App\Livewire\HR\IncidentForm')
            ->set('employee_id', $this->employee->id)
            ->set('incident_date', '')
            ->set('description', 'Descripción detallada de la incidencia.')
            ->call('save')
            ->assertHasErrors(['incident_date' => 'required']);
    }

    /** @test */
    public function can_update_incident(): void
    {
        $incident = HrIncident::create([
            'company_id'    => $this->user->company_id,
            'employee_id'   => $this->employee->id,
            'type'          => 'tardanza',
            'incident_date' => now()->format('Y-m-d'),
            'description'   => 'Descripción original de la incidencia.',
            'severity'      => 'low',
            'resolved'      => false,
            'created_by'    => $this->user->id,
        ]);

        Livewire::test('App\Livewire\HR\IncidentForm', ['incident' => $incident])
            ->set('resolved', true)
            ->set('action_taken', 'Se habló con el empleado y firmó carta compromiso.')
            ->call('save');

        $this->assertTrue($incident->fresh()->resolved);
        $this->assertNotNull($incident->fresh()->resolved_at);
    }
}
