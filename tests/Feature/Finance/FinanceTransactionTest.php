<?php

namespace Tests\Feature\Finance;

use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FinanceAccount $account;
    protected FinanceAccount $accountB;

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
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'is_active'       => true,
        ]);
        $this->accountB = FinanceAccount::create([
            'company_id'      => $company->id,
            'code'            => 'CAJA-001',
            'name'            => 'Caja Chica',
            'type'            => 'caja',
            'currency'        => 'MXN',
            'opening_balance' => 5000,
            'current_balance' => 5000,
            'is_active'       => true,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_ingreso_transaction(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('type', 'ingreso')
            ->set('concept', 'Cobro factura FV-000123')
            ->set('category', 'venta')
            ->set('amount', '58000')
            ->set('currency', 'MXN')
            ->set('exchange_rate', '1')
            ->set('transaction_date', now()->format('Y-m-d'))
            ->set('status', 'confirmado')
            ->call('save');

        $this->assertDatabaseHas('finance_transactions', [
            'account_id' => $this->account->id,
            'type'       => 'ingreso',
            'concept'    => 'Cobro factura FV-000123',
        ]);
    }

    /** @test */
    public function account_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', null)
            ->set('concept', 'Transacción')
            ->set('amount', '1000')
            ->call('save')
            ->assertHasErrors(['account_id' => 'required']);
    }

    /** @test */
    public function concept_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('concept', '')
            ->set('amount', '1000')
            ->call('save')
            ->assertHasErrors(['concept' => 'required']);
    }

    /** @test */
    public function amount_must_be_greater_than_zero(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Transacción')
            ->set('amount', '0')
            ->call('save')
            ->assertHasErrors(['amount']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Transacción')
            ->set('amount', '1000')
            ->set('type', 'abono')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function category_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Transacción')
            ->set('amount', '1000')
            ->set('category', 'gastos_fijos')
            ->call('save')
            ->assertHasErrors(['category']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('concept', 'Transacción')
            ->set('amount', '1000')
            ->set('status', 'autorizado')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function transfer_to_account_must_differ_from_source(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceTransactionForm')
            ->set('account_id', $this->account->id)
            ->set('transfer_to_account_id', $this->account->id)
            ->set('type', 'transferencia')
            ->set('concept', 'Transferencia interna')
            ->set('amount', '5000')
            ->set('transaction_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['transfer_to_account_id']);
    }

    /** @test */
    public function can_update_existing_transaction(): void
    {
        $txn = FinanceTransaction::create([
            'account_id'       => $this->account->id,
            'registered_by'    => $this->user->id,
            'folio'            => 'TXN-000001',
            'type'             => 'ingreso',
            'concept'          => 'Concepto original',
            'category'         => 'venta',
            'amount'           => 10000,
            'currency'         => 'MXN',
            'exchange_rate'    => 1,
            'transaction_date' => now()->toDateString(),
            'status'           => 'pendiente',
        ]);

        Livewire::test('App\Livewire\Finance\FinanceTransactionForm', ['transaction' => $txn])
            ->set('concept', 'Concepto actualizado')
            ->set('status', 'confirmado')
            ->call('save');

        $this->assertEquals('Concepto actualizado', $txn->fresh()->concept);
        $this->assertEquals('confirmado', $txn->fresh()->status);
    }
}
