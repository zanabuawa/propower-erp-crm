<?php

namespace Tests\Feature\Assets;

use App\Models\AssetMaintenance;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FixedAsset $asset;

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
        $this->asset = FixedAsset::create([
            'company_id'    => $company->id,
            'folio'         => 'ACT-000001',
            'name'          => 'Compresor Industrial',
            'status'        => 'active',
            'salvage_value' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_schedule_maintenance(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm', ['assetId' => $this->asset->id])
            ->set('fixed_asset_id', $this->asset->id)
            ->set('type', 'preventive')
            ->set('status', 'scheduled')
            ->set('scheduled_date', '2026-07-01')
            ->call('save');

        $this->assertDatabaseHas('asset_maintenances', [
            'fixed_asset_id' => $this->asset->id,
            'type'           => 'preventive',
            'status'         => 'scheduled',
            'company_id'     => $this->user->company_id,
        ]);
    }

    /** @test */
    public function asset_is_required(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm')
            ->set('fixed_asset_id', null)
            ->set('scheduled_date', '2026-07-01')
            ->call('save')
            ->assertHasErrors(['fixed_asset_id' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm')
            ->set('fixed_asset_id', $this->asset->id)
            ->set('type', 'mejora')
            ->set('scheduled_date', '2026-07-01')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm')
            ->set('fixed_asset_id', $this->asset->id)
            ->set('status', 'pendiente')
            ->set('scheduled_date', '2026-07-01')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function cost_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm')
            ->set('fixed_asset_id', $this->asset->id)
            ->set('scheduled_date', '2026-07-01')
            ->set('cost', '-2000')
            ->call('save')
            ->assertHasErrors(['cost']);
    }

    /** @test */
    public function completed_date_must_be_after_scheduled_date(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm')
            ->set('fixed_asset_id', $this->asset->id)
            ->set('scheduled_date', '2026-07-15')
            ->set('completed_date', '2026-07-10')
            ->call('save')
            ->assertHasErrors(['completed_date']);
    }

    /** @test */
    public function in_progress_status_marks_asset_as_in_maintenance(): void
    {
        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm', ['assetId' => $this->asset->id])
            ->set('fixed_asset_id', $this->asset->id)
            ->set('type', 'corrective')
            ->set('status', 'in_progress')
            ->set('scheduled_date', now()->format('Y-m-d'))
            ->call('save');

        $this->assertEquals('in_maintenance', $this->asset->fresh()->status);
    }

    /** @test */
    public function can_update_maintenance(): void
    {
        $maintenance = AssetMaintenance::create([
            'company_id'     => $this->user->company_id,
            'fixed_asset_id' => $this->asset->id,
            'created_by'     => $this->user->id,
            'folio'          => 'MNT-000001',
            'type'           => 'preventive',
            'status'         => 'scheduled',
            'scheduled_date' => '2026-07-01',
        ]);

        Livewire::test('App\Livewire\Assets\AssetMaintenanceForm', ['maintenance' => $maintenance])
            ->set('status', 'completed')
            ->set('completed_date', '2026-07-01')
            ->set('work_performed', 'Lubricación y revisión de válvulas.')
            ->call('save');

        $this->assertEquals('completed', $maintenance->fresh()->status);
        $this->assertNotNull($maintenance->fresh()->completed_date);
    }
}
