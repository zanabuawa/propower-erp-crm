<?php

namespace Tests\Feature\Finance;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
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
        $this->branch = Branch::create([
            'company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_finance_account(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', 'BBVA-001')
            ->set('name', 'BBVA Cuenta Corriente')
            ->set('type', 'banco')
            ->set('currency', 'MXN')
            ->set('opening_balance', '50000')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('finance_accounts', [
            'code'       => 'BBVA-001',
            'name'       => 'BBVA Cuenta Corriente',
            'type'       => 'banco',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function opening_balance_becomes_current_balance_on_create(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', 'CAJA-001')
            ->set('name', 'Caja Chica')
            ->set('type', 'caja')
            ->set('currency', 'MXN')
            ->set('opening_balance', '5000')
            ->call('save');

        $account = FinanceAccount::where('code', 'CAJA-001')->first();
        $this->assertNotNull($account);
        $this->assertEquals(5000, $account->opening_balance);
        $this->assertEquals(5000, $account->current_balance);
    }

    /** @test */
    public function code_and_name_are_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', '')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['code' => 'required', 'name' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', 'ACC-001')
            ->set('name', 'Cuenta')
            ->set('type', 'cheque')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function code_must_be_unique(): void
    {
        FinanceAccount::create([
            'company_id'      => $this->user->company_id,
            'code'            => 'UNICO-001',
            'name'            => 'Cuenta Existente',
            'type'            => 'banco',
            'currency'        => 'MXN',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active'       => true,
        ]);

        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', 'UNICO-001')
            ->set('name', 'Otra Cuenta')
            ->set('type', 'banco')
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);
    }

    /** @test */
    public function code_is_not_unique_when_updating_same_account(): void
    {
        $account = FinanceAccount::create([
            'company_id'      => $this->user->company_id,
            'code'            => 'EDIT-001',
            'name'            => 'Cuenta Original',
            'type'            => 'banco',
            'currency'        => 'MXN',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active'       => true,
        ]);

        Livewire::test('App\Livewire\Finance\FinanceAccountForm', ['account' => $account])
            ->set('name', 'Cuenta Editada')
            ->call('save')
            ->assertHasNoErrors(['code']);

        $this->assertEquals('Cuenta Editada', $account->fresh()->name);
    }

    /** @test */
    public function opening_balance_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', 'ACC-001')
            ->set('name', 'Cuenta')
            ->set('type', 'banco')
            ->set('opening_balance', '-1000')
            ->call('save')
            ->assertHasErrors(['opening_balance']);
    }

    /** @test */
    public function can_create_account_with_branch(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceAccountForm')
            ->set('code', 'SUC-001')
            ->set('name', 'Caja Sucursal')
            ->set('type', 'caja')
            ->set('currency', 'MXN')
            ->set('branch_id', $this->branch->id)
            ->call('save');

        $this->assertDatabaseHas('finance_accounts', [
            'code'      => 'SUC-001',
            'branch_id' => $this->branch->id,
        ]);
    }
}
