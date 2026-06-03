<?php

namespace Tests\Feature\Finance;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\TravelExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TravelExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected HrEmployee $employee;

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
        $this->employee = HrEmployee::create([
            'company_id'    => $company->id,
            'first_name'    => 'Miguel',
            'last_name'     => 'Hernández',
            'hire_date'     => now()->subYear(),
            'contract_type' => 'indefinido',
            'salary'        => 30000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_travel_expense(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseForm')
            ->set('employee_id', $this->employee->id)
            ->set('destination', 'Ciudad de México')
            ->set('purpose', 'Reunión con cliente corporativo para presentación de propuesta.')
            ->set('departure_date', '2026-06-10')
            ->set('return_date', '2026-06-12')
            ->set('amount_approved', '8000')
            ->set('currency', 'MXN')
            ->set('items', [[
                'id'             => null,
                'category'       => 'transporte',
                'concept'        => 'Vuelo MTY-MEX',
                'amount'         => '3500',
                'receipt_number' => 'TK-001',
                'notes'          => '',
            ]])
            ->call('save');

        $this->assertDatabaseHas('travel_expenses', [
            'employee_id' => $this->employee->id,
            'destination' => 'Ciudad de México',
            'status'      => 'borrador',
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseForm')
            ->set('employee_id', null)
            ->set('destination', 'CDMX')
            ->set('purpose', 'Reunión.')
            ->set('amount_approved', '5000')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function destination_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseForm')
            ->set('employee_id', $this->employee->id)
            ->set('destination', '')
            ->set('purpose', 'Reunión.')
            ->set('amount_approved', '5000')
            ->call('save')
            ->assertHasErrors(['destination' => 'required']);
    }

    /** @test */
    public function return_date_must_be_after_departure(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseForm')
            ->set('employee_id', $this->employee->id)
            ->set('destination', 'Guadalajara')
            ->set('purpose', 'Auditoría.')
            ->set('departure_date', '2026-06-15')
            ->set('return_date', '2026-06-10')
            ->set('amount_approved', '5000')
            ->call('save')
            ->assertHasErrors(['return_date']);
    }

    /** @test */
    public function currency_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseForm')
            ->set('employee_id', $this->employee->id)
            ->set('destination', 'Guadalajara')
            ->set('purpose', 'Auditoría.')
            ->set('amount_approved', '5000')
            ->set('currency', 'GBP')
            ->call('save')
            ->assertHasErrors(['currency']);
    }

    /** @test */
    public function folio_is_generated_with_via_prefix(): void
    {
        Livewire::test('App\Livewire\Finance\TravelExpenseForm')
            ->set('employee_id', $this->employee->id)
            ->set('destination', 'Monterrey')
            ->set('purpose', 'Visita a instalaciones.')
            ->set('departure_date', '2026-07-01')
            ->set('return_date', '2026-07-02')
            ->set('amount_approved', '3000')
            ->set('currency', 'MXN')
            ->set('items', [[
                'id'             => null,
                'category'       => 'hospedaje',
                'concept'        => 'Hotel una noche',
                'amount'         => '1200',
                'receipt_number' => '',
                'notes'          => '',
            ]])
            ->call('save');

        $expense = TravelExpense::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($expense);
        $this->assertStringStartsWith('VIA-', $expense->folio);
    }
}
