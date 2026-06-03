<?php

namespace Tests\Feature\Finance;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinanceBudget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_budget(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto Q1 2026')
            ->set('period_type', 'trimestral')
            ->set('year', '2026')
            ->set('period_number', '1')
            ->set('category', 'egresos')
            ->set('amount_planned', '500000')
            ->set('currency', 'MXN')
            ->set('status', 'borrador')
            ->call('save');

        $this->assertDatabaseHas('finance_budgets', [
            'name'          => 'Presupuesto Q1 2026',
            'period_type'   => 'trimestral',
            'category'      => 'egresos',
            'company_id'    => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function period_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto')
            ->set('period_type', 'quincenal')
            ->call('save')
            ->assertHasErrors(['period_type']);
    }

    /** @test */
    public function category_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto')
            ->set('category', 'gastos_fijos')
            ->call('save')
            ->assertHasErrors(['category']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto')
            ->set('status', 'en_revision')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function amount_planned_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto')
            ->set('amount_planned', '-5000')
            ->call('save')
            ->assertHasErrors(['amount_planned']);
    }

    /** @test */
    public function year_must_be_in_valid_range(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto')
            ->set('year', '1999')
            ->call('save')
            ->assertHasErrors(['year']);
    }

    /** @test */
    public function can_update_existing_budget(): void
    {
        $budget = FinanceBudget::create([
            'company_id'     => $this->user->company_id,
            'name'           => 'Presupuesto Original',
            'period_type'    => 'mensual',
            'year'           => 2026,
            'category'       => 'otro',
            'amount_planned' => 100000,
            'currency'       => 'MXN',
            'status'         => 'borrador',
        ]);

        Livewire::test('App\Livewire\Finance\FinanceBudgetForm', ['budget' => $budget])
            ->set('name', 'Presupuesto Actualizado')
            ->set('amount_planned', '200000')
            ->set('status', 'aprobado')
            ->call('save');

        $this->assertEquals('Presupuesto Actualizado', $budget->fresh()->name);
        $this->assertEquals(200000, $budget->fresh()->amount_planned);
        $this->assertEquals('aprobado', $budget->fresh()->status);
    }

    /** @test */
    public function all_valid_categories_are_accepted(): void
    {
        foreach (['ingresos', 'egresos', 'proyecto', 'departamento', 'otro'] as $category) {
            Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
                ->set('name', 'Presupuesto')
                ->set('category', $category)
                ->assertHasNoErrors(['category']);
        }
    }

    /** @test */
    public function can_create_budget_with_zero_amount(): void
    {
        Livewire::test('App\Livewire\Finance\FinanceBudgetForm')
            ->set('name', 'Presupuesto Vacío')
            ->set('period_type', 'anual')
            ->set('year', '2026')
            ->set('category', 'ingresos')
            ->set('amount_planned', '0')
            ->set('currency', 'MXN')
            ->set('status', 'borrador')
            ->call('save');

        $this->assertDatabaseHas('finance_budgets', [
            'name'           => 'Presupuesto Vacío',
            'amount_planned' => 0,
        ]);
    }
}
