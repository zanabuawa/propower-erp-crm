<?php

namespace Tests\Feature\Branches;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::create([
            'name' => 'Test Co', 'legal_name' => 'Test Co S.A.', 'rfc' => 'ABC123456789',
        ]);
        $this->user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'company_id' => $this->company->id,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_branch(): void
    {
        Livewire::test('App\Livewire\Branches\BranchForm')
            ->set('company_id', $this->company->id)
            ->set('name', 'Sucursal Norte')
            ->set('code', 'SN01')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('branches', [
            'name'       => 'Sucursal Norte',
            'company_id' => $this->company->id,
        ]);
    }

    /** @test */
    public function company_and_name_are_required(): void
    {
        Livewire::test('App\Livewire\Branches\BranchForm')
            ->set('company_id', null)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['company_id' => 'required', 'name' => 'required']);
    }

    /** @test */
    public function email_must_be_valid_format(): void
    {
        Livewire::test('App\Livewire\Branches\BranchForm')
            ->set('company_id', $this->company->id)
            ->set('name', 'Sucursal')
            ->set('email', 'no-es-email')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    /** @test */
    public function can_update_branch(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name'       => 'Sucursal Original',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Branches\BranchForm', ['branch' => $branch])
            ->set('name', 'Sucursal Actualizada')
            ->set('city', 'Monterrey')
            ->set('state', 'Nuevo León')
            ->call('save');

        $this->assertEquals('Sucursal Actualizada', $branch->fresh()->name);
        $this->assertEquals('Monterrey', $branch->fresh()->city);
    }

    /** @test */
    public function can_deactivate_branch(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name'       => 'Sucursal Activa',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Branches\BranchForm', ['branch' => $branch])
            ->set('is_active', false)
            ->call('save');

        $this->assertFalse($branch->fresh()->is_active);
    }
}
