<?php

namespace Tests\Feature\Assets;

use App\Models\AssetMaintenance;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetMaintenanceIndexTest extends TestCase
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
            'name'          => 'Generador 50kVA',
            'status'        => 'active',
            'salvage_value' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function complete_changes_maintenance_status(): void
    {
        $maintenance = AssetMaintenance::create([
            'company_id'     => $this->user->company_id,
            'fixed_asset_id' => $this->asset->id,
            'created_by'     => $this->user->id,
            'folio'          => 'MNT-0001',
            'type'           => 'preventivo',
            'status'         => 'scheduled',
            'scheduled_date' => now()->addDays(7)->toDateString(),
        ]);

        Livewire::test('App\Livewire\Assets\AssetMaintenanceIndex')
            ->call('complete', $maintenance->id);

        $this->assertEquals('completed', $maintenance->fresh()->status);
    }

    /** @test */
    public function cancel_changes_maintenance_status(): void
    {
        $maintenance = AssetMaintenance::create([
            'company_id'     => $this->user->company_id,
            'fixed_asset_id' => $this->asset->id,
            'created_by'     => $this->user->id,
            'folio'          => 'MNT-0002',
            'type'           => 'correctivo',
            'status'         => 'scheduled',
            'scheduled_date' => now()->addDays(3)->toDateString(),
        ]);

        Livewire::test('App\Livewire\Assets\AssetMaintenanceIndex')
            ->call('cancel', $maintenance->id);

        $this->assertEquals('cancelled', $maintenance->fresh()->status);
    }
}
