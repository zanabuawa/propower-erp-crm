<?php

namespace Tests\Feature\Assets;

use App\Models\AssetLoan;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetLoanTest extends TestCase
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
            'name'          => 'Laptop Dell XPS 15',
            'status'        => 'active',
            'salvage_value' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_loan_to_internal_user(): void
    {
        Livewire::test('App\Livewire\Assets\AssetLoanForm', ['assetId' => $this->asset->id])
            ->set('assetId', $this->asset->id)
            ->set('recipientType', 'user')
            ->set('loanedToUserId', $this->user->id)
            ->set('loanDate', '2026-06-01')
            ->set('conditionOnLoan', 'good')
            ->set('purpose', 'Trabajo remoto temporal.')
            ->call('save');

        $this->assertDatabaseHas('asset_loans', [
            'fixed_asset_id'    => $this->asset->id,
            'loaned_to_user_id' => $this->user->id,
            'status'            => 'active',
        ]);
    }

    /** @test */
    public function asset_is_required(): void
    {
        Livewire::test('App\Livewire\Assets\AssetLoanForm')
            ->set('assetId', null)
            ->set('recipientType', 'user')
            ->set('loanedToUserId', $this->user->id)
            ->set('loanDate', '2026-06-01')
            ->call('save')
            ->assertHasErrors(['assetId' => 'required']);
    }

    /** @test */
    public function recipient_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Assets\AssetLoanForm', ['assetId' => $this->asset->id])
            ->set('assetId', $this->asset->id)
            ->set('recipientType', 'empresa')
            ->set('loanDate', '2026-06-01')
            ->call('save')
            ->assertHasErrors(['recipientType']);
    }

    /** @test */
    public function external_loan_requires_name(): void
    {
        Livewire::test('App\Livewire\Assets\AssetLoanForm', ['assetId' => $this->asset->id])
            ->set('assetId', $this->asset->id)
            ->set('recipientType', 'external')
            ->set('loanedToName', '')
            ->set('loanDate', '2026-06-01')
            ->set('conditionOnLoan', 'good')
            ->call('save')
            ->assertHasErrors(['loanedToName']);
    }

    /** @test */
    public function condition_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Assets\AssetLoanForm', ['assetId' => $this->asset->id])
            ->set('assetId', $this->asset->id)
            ->set('recipientType', 'user')
            ->set('loanedToUserId', $this->user->id)
            ->set('loanDate', '2026-06-01')
            ->set('conditionOnLoan', 'excelente')
            ->call('save')
            ->assertHasErrors(['conditionOnLoan']);
    }

    /** @test */
    public function loan_changes_asset_status_to_transferred(): void
    {
        Livewire::test('App\Livewire\Assets\AssetLoanForm', ['assetId' => $this->asset->id])
            ->set('assetId', $this->asset->id)
            ->set('recipientType', 'user')
            ->set('loanedToUserId', $this->user->id)
            ->set('loanDate', '2026-06-01')
            ->set('conditionOnLoan', 'good')
            ->call('save');

        $this->assertEquals('transferred', $this->asset->fresh()->status);
    }
}
