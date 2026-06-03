<?php

namespace Tests\Feature\Assets;

use App\Models\AssetTransfer;
use App\Models\Branch;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FixedAsset $asset;
    protected Branch $branch;

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
        $branchA = Branch::create([
            'company_id' => $company->id, 'name' => 'Sucursal Norte', 'is_active' => true,
        ]);
        $this->branch = Branch::create([
            'company_id' => $company->id, 'name' => 'Sucursal Sur', 'is_active' => true,
        ]);
        $this->asset = FixedAsset::create([
            'company_id'    => $company->id,
            'branch_id'     => $branchA->id,
            'folio'         => 'ACT-000001',
            'name'          => 'Laptop Dell XPS',
            'status'        => 'active',
            'salvage_value' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_asset_transfer(): void
    {
        Livewire::test('App\Livewire\Assets\AssetTransferForm')
            ->set('asset_id', $this->asset->id)
            ->set('selectedAsset', $this->asset)
            ->set('to_branch_id', $this->branch->id)
            ->set('transferred_at', now()->format('Y-m-d\TH:i'))
            ->set('reason', 'Reasignación por reestructura organizacional.')
            ->call('save');

        $this->assertDatabaseHas('asset_transfers', [
            'asset_id'     => $this->asset->id,
            'to_branch_id' => $this->branch->id,
            'status'       => 'completed',
            'company_id'   => $this->user->company_id,
        ]);
    }

    /** @test */
    public function asset_is_required(): void
    {
        Livewire::test('App\Livewire\Assets\AssetTransferForm')
            ->set('asset_id', null)
            ->set('transferred_at', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['asset_id' => 'required']);
    }

    /** @test */
    public function transferred_at_is_required(): void
    {
        Livewire::test('App\Livewire\Assets\AssetTransferForm')
            ->set('asset_id', $this->asset->id)
            ->set('transferred_at', '')
            ->call('save')
            ->assertHasErrors(['transferred_at' => 'required']);
    }

    /** @test */
    public function transfer_updates_asset_location(): void
    {
        Livewire::test('App\Livewire\Assets\AssetTransferForm')
            ->set('asset_id', $this->asset->id)
            ->set('selectedAsset', $this->asset)
            ->set('to_branch_id', $this->branch->id)
            ->set('transferred_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertEquals($this->branch->id, $this->asset->fresh()->branch_id);
    }

    /** @test */
    public function folio_is_generated_on_create(): void
    {
        Livewire::test('App\Livewire\Assets\AssetTransferForm')
            ->set('asset_id', $this->asset->id)
            ->set('selectedAsset', $this->asset)
            ->set('to_branch_id', $this->branch->id)
            ->set('transferred_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $transfer = AssetTransfer::first();
        $this->assertNotNull($transfer);
        $this->assertNotNull($transfer->folio);
    }

    /** @test */
    public function can_search_asset(): void
    {
        $component = Livewire::test('App\Livewire\Assets\AssetTransferForm')
            ->set('assetSearch', 'Laptop');

        $this->assertNotEmpty($component->get('assetResults'));
    }
}
