<?php

namespace Tests\Feature\Finance;

use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\FinanceCashflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceCashflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FinanceAccount $account;

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
        $this->account = FinanceAccount::create([
            'company_id'      => $company->id,
            'code'            => 'BBVA-001',
            'name'            => 'BBVA Cuenta',
            'type'            => 'banco',
            'currency'        => 'MXN',
            'opening_balance' => 100000,
            'current_balance' => 100000,
            'is_active'       => true,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_cashflow_entry(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Cobro proyectado cliente ABC')
            ->set('type', 'proyectado')
            ->set('flow', 'entrada')
            ->set('category', 'operacion')
            ->set('amount', '120000')
            ->set('currency', 'MXN')
            ->set('expected_date', '2026-07-15')
            ->call('save');

        $this->assertDatabaseHas('finance_cashflow', [
            'concept'  => 'Cobro proyectado cliente ABC',
            'flow'     => 'entrada',
            'type'     => 'proyectado',
            'category' => 'operacion',
        ]);
    }

    /** @test */
    public function account_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', null)
            ->set('concept', 'Movimiento')
            ->set('amount', '1000')
            ->call('save')
            ->assertHasErrors(['account_id' => 'required']);
    }

    /** @test */
    public function concept_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', '')
            ->set('amount', '1000')
            ->call('save')
            ->assertHasErrors(['concept' => 'required']);
    }

    /** @test */
    public function amount_must_be_greater_than_zero(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Movimiento')
            ->set('amount', '0')
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    /** @test */
    public function flow_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Movimiento')
            ->set('amount', '1000')
            ->set('flow', 'mixto')
            ->call('save')
            ->assertHasErrors(['flow']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Movimiento')
            ->set('amount', '1000')
            ->set('type', 'estimado')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function category_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Movimiento')
            ->set('amount', '1000')
            ->set('category', 'gastos_admin')
            ->call('save')
            ->assertHasErrors(['category']);
    }

    /** @test */
    public function expected_date_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceCashflowForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Movimiento')
            ->set('amount', '1000')
            ->set('expected_date', '')
            ->call('save')
            ->assertHasErrors(['expected_date' => 'required']);
    }

    /** @test */
    public function can_update_existing_cashflow(): void
    {
        $cf = FinanceCashflow::create([
            'account_id'    => $this->account->id,
            'concept'       => 'Movimiento original',
            'type'          => 'proyectado',
            'flow'          => 'entrada',
            'category'      => 'operacion',
            'amount'        => 50000,
            'currency'      => 'MXN',
            'expected_date' => '2026-07-01',
            'is_realized'   => false,
        ]);

        Livewire::test('App\Livewire\Finance\FinanceCashflowForm', ['cashflow' => $cf])
            ->set('concept', 'Movimiento actualizado')
            ->set('amount', '75000')
            ->call('save');

        $this->assertEquals('Movimiento actualizado', $cf->fresh()->concept);
        $this->assertEquals(75000, $cf->fresh()->amount);
    }
}
