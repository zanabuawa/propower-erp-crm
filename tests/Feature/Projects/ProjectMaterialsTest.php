<?php

namespace Tests\Feature\Projects;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectMaterialsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

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
        $this->project = Project::create([
            'code'   => 'PRY-001',
            'name'   => 'Proyecto Solar Monterrey',
            'status' => 'activo',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_add_material_to_project(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectMaterials', ['project' => $this->project])
            ->call('openCreate')
            ->set('name', 'Cable THHN 12 AWG')
            ->set('resource_type', 'material')
            ->set('quantity_needed', '100')
            ->set('quantity_used', '0')
            ->set('unit_cost', '15.50')
            ->call('save');

        $this->assertDatabaseHas('project_materials', [
            'project_id'    => $this->project->id,
            'name'          => 'Cable THHN 12 AWG',
            'resource_type' => 'material',
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectMaterials', ['project' => $this->project])
            ->call('openCreate')
            ->set('name', '')
            ->set('resource_type', 'material')
            ->set('quantity_needed', '10')
            ->set('quantity_used', '0')
            ->set('unit_cost', '100')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function resource_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectMaterials', ['project' => $this->project])
            ->call('openCreate')
            ->set('name', 'Recurso Inválido')
            ->set('resource_type', 'servicio')
            ->set('quantity_needed', '10')
            ->set('quantity_used', '0')
            ->set('unit_cost', '100')
            ->call('save')
            ->assertHasErrors(['resource_type']);
    }

    /** @test */
    public function quantity_needed_must_be_positive(): void
    {
        Livewire::test('App\Livewire\Projects\ProjectMaterials', ['project' => $this->project])
            ->call('openCreate')
            ->set('name', 'Herramienta')
            ->set('resource_type', 'herramienta')
            ->set('quantity_needed', '0')
            ->set('quantity_used', '0')
            ->set('unit_cost', '0')
            ->call('save')
            ->assertHasErrors(['quantity_needed']);
    }

    /** @test */
    public function can_update_existing_material(): void
    {
        $material = ProjectMaterial::create([
            'project_id'        => $this->project->id,
            'name'              => 'Tornillo hexagonal',
            'resource_type'     => 'material',
            'quantity_needed'   => 200,
            'quantity_reserved' => 0,
            'quantity_used'     => 0,
            'unit_cost'         => 0.5,
            'status'            => 'pendiente',
        ]);

        Livewire::test('App\Livewire\Projects\ProjectMaterials', ['project' => $this->project])
            ->call('openEdit', $material->id)
            ->set('quantity_used', '50')
            ->set('status', 'adquirido')
            ->call('save');

        $this->assertEquals(50, $material->fresh()->quantity_used);
    }
}
