<?php

namespace Tests\Feature\Branches;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BranchIndexTest extends TestCase
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
    public function can_delete_branch(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name'       => 'Sucursal Norte',
            'code'       => 'SUC-N',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Branches\BranchIndex')
            ->call('confirmDelete', $branch->id)
            ->call('delete');

        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    /** @test */
    public function cancel_delete_keeps_branch(): void
    {
        $branch = Branch::create([
            'company_id' => $this->company->id,
            'name'       => 'Sucursal Sur',
            'code'       => 'SUC-S',
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\Branches\BranchIndex')
            ->call('confirmDelete', $branch->id)
            ->call('cancelDelete');

        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }
}
