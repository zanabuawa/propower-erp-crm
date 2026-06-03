<?php

namespace Tests\Feature\Assets;

use App\Models\AssetLoan;
use App\Models\Company;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetLoanIndexTest extends TestCase
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
            'status'        => 'transferred',
            'salvage_value' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function confirm_return_marks_loan_as_returned(): void
    {
        $loan = AssetLoan::create([
            'company_id'        => $this->user->company_id,
            'fixed_asset_id'    => $this->asset->id,
            'loaned_to_user_id' => $this->user->id,
            'created_by'        => $this->user->id,
            'folio'             => 'LOAN-0001',
            'loan_date'         => now()->subDays(5)->toDateString(),
            'condition_on_loan' => 'good',
            'status'            => 'active',
        ]);

        Livewire::test('App\Livewire\Assets\AssetLoanIndex')
            ->call('openReturnModal', $loan->id)
            ->set('conditionOnReturn', 'good')
            ->call('confirmReturn');

        $this->assertEquals('returned', $loan->fresh()->status);
    }

    /** @test */
    public function mark_lost_changes_loan_status_to_lost(): void
    {
        $loan = AssetLoan::create([
            'company_id'        => $this->user->company_id,
            'fixed_asset_id'    => $this->asset->id,
            'loaned_to_user_id' => $this->user->id,
            'created_by'        => $this->user->id,
            'folio'             => 'LOAN-0002',
            'loan_date'         => now()->subDays(10)->toDateString(),
            'condition_on_loan' => 'good',
            'status'            => 'active',
        ]);

        Livewire::test('App\Livewire\Assets\AssetLoanIndex')
            ->call('markLost', $loan->id);

        $this->assertEquals('lost', $loan->fresh()->status);
    }
}
