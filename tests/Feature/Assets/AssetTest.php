<?php

namespace Tests\Feature\Assets;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_asset(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Compresor Industrial')
            ->set('category', 'maquinaria')
            ->set('status', 'active')
            ->set('acquisition_date', '2026-01-15')
            ->set('acquisition_cost', '250000')
            ->call('save');

        $this->assertDatabaseHas('fixed_assets', [
            'name'             => 'Compresor Industrial',
            'category'         => 'maquinaria',
            'status'           => 'active',
            'company_id'       => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Equipo')
            ->set('status', 'broken')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function depreciation_method_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Equipo')
            ->set('depreciation_method', 'unitario')
            ->call('save')
            ->assertHasErrors(['depreciation_method']);
    }

    /** @test */
    public function acquisition_cost_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Equipo')
            ->set('acquisition_cost', '-1000')
            ->call('save')
            ->assertHasErrors(['acquisition_cost']);
    }

    /** @test */
    public function salvage_value_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Equipo')
            ->set('salvage_value', '-500')
            ->call('save')
            ->assertHasErrors(['salvage_value']);
    }

    /** @test */
    public function fiscal_rate_must_be_between_0_and_100(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Equipo')
            ->set('fiscal_rate', '150')
            ->call('save')
            ->assertHasErrors(['fiscal_rate']);
    }

    /** @test */
    public function can_create_asset_with_depreciation(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Vehículo de Reparto')
            ->set('status', 'active')
            ->set('acquisition_cost', '450000')
            ->set('depreciation_method', 'linea_recta')
            ->set('useful_life_years', '5')
            ->set('salvage_value', '50000')
            ->set('fiscal_rate', '25')
            ->call('save');

        $asset = FixedAsset::where('name', 'Vehículo de Reparto')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('linea_recta', $asset->depreciation_method);
        $this->assertEquals(5, $asset->useful_life_years);
        $this->assertEquals(0.25, $asset->fiscal_rate);
    }

    /** @test */
    public function folio_is_generated_on_create(): void
    {
        Livewire::test('App\Livewire\Assets\AssetForm')
            ->set('name', 'Monitor')
            ->set('status', 'active')
            ->call('save');

        $asset = FixedAsset::where('name', 'Monitor')->first();
        $this->assertNotNull($asset);
        $this->assertNotNull($asset->folio);
    }

    /** @test */
    public function can_update_existing_asset(): void
    {
        $asset = FixedAsset::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Activo Original',
            'status'     => 'active',
            'folio'      => 'ACT-000001',
            'salvage_value' => 0,
        ]);

        Livewire::test('App\Livewire\Assets\AssetForm', ['asset' => $asset])
            ->set('name', 'Activo Actualizado')
            ->set('status', 'in_maintenance')
            ->call('save');

        $this->assertEquals('Activo Actualizado', $asset->fresh()->name);
        $this->assertEquals('in_maintenance', $asset->fresh()->status);
    }

    /** @test */
    public function all_valid_statuses_are_accepted(): void
    {
        foreach (['active', 'in_maintenance', 'transferred', 'retired'] as $status) {
            $component = Livewire::test('App\Livewire\Assets\AssetForm')
                ->set('name', 'Equipo')
                ->set('status', $status);

            $component->assertHasNoErrors(['status']);
        }
    }
}
