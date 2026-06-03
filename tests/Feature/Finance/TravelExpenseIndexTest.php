<?php

namespace Tests\Feature\Finance;

use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\HrEmployee;
use App\Models\TravelExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TravelExpenseIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected TravelExpense $expense;
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
            'company_id' => $company->id,
            'code'       => 'CAJA-01',
            'name'       => 'Caja General',
            'type'       => 'caja',
            'currency'   => 'MXN',
            'is_active'  => true,
        ]);
        $employee = HrEmployee::create([
            'company_id'    => $company->id,
            'first_name'    => 'Luis',
            'last_name'     => 'Garza',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 12000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->expense = TravelExpense::create([
            'company_id'     => $company->id,
            'employee_id'    => $employee->id,
            'assigned_by'    => $this->user->id,
            'folio'          => 'VIA-00001',
            'destination'    => 'Monterrey, NL',
            'purpose'        => 'Reunión con cliente',
            'departure_date' => now()->addDays(3)->toDateString(),
            'return_date'    => now()->addDays(5)->toDateString(),
            'status'         => 'borrador',
            'currency'       => 'MXN',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function approve_changes_expense_status(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseIndex')
            ->call('openApprove', $this->expense->id)
            ->set('approveAccountId', $this->account->id)
            ->call('approve');

        $this->assertEquals('aprobado', $this->expense->fresh()->status);
    }

    /** @test */
    public function approve_requires_finance_account(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseIndex')
            ->call('openApprove', $this->expense->id)
            ->set('approveAccountId', null)
            ->call('approve')
            ->assertHasErrors(['approveAccountId']);
    }

    /** @test */
    public function reject_changes_expense_status(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseIndex')
            ->call('openReject', $this->expense->id)
            ->set('rejectReason', 'Presupuesto insuficiente para el mes.')
            ->call('reject');

        $this->assertEquals('rechazado', $this->expense->fresh()->status);
    }

    /** @test */
    public function reject_requires_reason(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseIndex')
            ->call('openReject', $this->expense->id)
            ->set('rejectReason', '')
            ->call('reject')
            ->assertHasErrors(['rejectReason']);
    }
}
