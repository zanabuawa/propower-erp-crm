<?php

namespace Tests\Feature\Projects;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

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
        $this->customer = Customer::create([
            'company_id' => $company->id, 'name' => 'Cliente Test',
            'status' => 'active', 'credit_limit' => 0, 'payment_terms' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_project(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', 'PROJ-0001')
            ->set('name', 'Instalación Planta Norte')
            ->set('type', 'instalacion')
            ->set('status', 'borrador')
            ->set('currency', 'MXN')
            ->call('save');

        $this->assertDatabaseHas('projects', [
            'name'   => 'Instalación Planta Norte',
            'type'   => 'instalacion',
            'status' => 'borrador',
        ]);
    }

    /** @test */
    public function code_and_name_are_required(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', '')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['code' => 'required', 'name' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', 'PROJ-0001')
            ->set('name', 'Proyecto')
            ->set('type', 'invalido')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', 'PROJ-0001')
            ->set('name', 'Proyecto')
            ->set('status', 'en_proceso')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function end_date_must_be_after_or_equal_start_date(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', 'PROJ-0001')
            ->set('name', 'Proyecto')
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2026-05-01')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    /** @test */
    public function code_must_be_unique(): void
    {
        Project::create([
            'company_id' => $this->user->company_id,
            'code'       => 'PROJ-UNICO',
            'name'       => 'Proyecto Existente',
            'type'       => 'externo',
            'status'     => 'borrador',
            'currency'   => 'MXN',
        ]);

        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', 'PROJ-UNICO')
            ->set('name', 'Otro Proyecto')
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);
    }

    /** @test */
    public function can_update_existing_project(): void
    {
        $project = Project::create([
            'company_id' => $this->user->company_id,
            'code'       => 'PROJ-EDIT',
            'name'       => 'Proyecto Original',
            'type'       => 'externo',
            'status'     => 'borrador',
            'currency'   => 'MXN',
        ]);

        Livewire::test('App\Livewire\Projects\ProjectForm', ['project' => $project])
            ->set('name', 'Proyecto Editado')
            ->set('status', 'activo')
            ->set('budget', '500000')
            ->call('save');

        $this->assertEquals('Proyecto Editado', $project->fresh()->name);
        $this->assertEquals('activo', $project->fresh()->status);
    }

    /** @test */
    public function budget_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectForm')
            ->set('code', 'PROJ-0001')
            ->set('name', 'Proyecto')
            ->set('budget', '-5000')
            ->call('save')
            ->assertHasErrors(['budget']);
    }
}
