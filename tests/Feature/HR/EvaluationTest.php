<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrEmployee;
use App\Models\HrPerformanceEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EvaluationTest extends TestCase
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
            'first_name'    => 'Ana',
            'last_name'     => 'Torres',
            'hire_date'     => now()->subYears(2),
            'contract_type' => 'indefinido',
            'salary'        => 25000,
            'salary_period' => 'monthly',
            'status'        => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_evaluation(): void
    {
        Livewire::test('App\Livewire\HR\EvaluationForm')
            ->set('employee_id', $this->employee->id)
            ->set('period', '2026-Q2')
            ->set('evaluation_date', '2026-06-30')
            ->set('status', 'draft')
            ->call('save');

        $this->assertDatabaseHas('hr_performance_evaluations', [
            'employee_id' => $this->employee->id,
            'period'      => '2026-Q2',
            'status'      => 'draft',
        ]);
    }

    /** @test */
    public function employee_is_required(): void
    {
        Livewire::test('App\Livewire\HR\EvaluationForm')
            ->set('employee_id', null)
            ->set('period', '2026-Q2')
            ->set('evaluation_date', '2026-06-30')
            ->call('save')
            ->assertHasErrors(['employee_id' => 'required']);
    }

    /** @test */
    public function period_is_required(): void
    {
        Livewire::test('App\Livewire\HR\EvaluationForm')
            ->set('employee_id', $this->employee->id)
            ->set('period', '')
            ->set('evaluation_date', '2026-06-30')
            ->call('save')
            ->assertHasErrors(['period' => 'required']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\EvaluationForm')
            ->set('employee_id', $this->employee->id)
            ->set('period', '2026-Q2')
            ->set('evaluation_date', '2026-06-30')
            ->set('status', 'pending')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function category_score_must_be_within_range(): void
    {
        Livewire::test('App\Livewire\HR\EvaluationForm')
            ->set('employee_id', $this->employee->id)
            ->set('period', '2026-Q2')
            ->set('evaluation_date', '2026-06-30')
            ->set('categories.attendance', 150)
            ->call('save')
            ->assertHasErrors(['categories.attendance']);
    }

    /** @test */
    public function overall_score_is_calculated_on_save(): void
    {
        Livewire::test('App\Livewire\HR\EvaluationForm')
            ->set('employee_id', $this->employee->id)
            ->set('period', '2026-Q2')
            ->set('evaluation_date', '2026-06-30')
            ->set('categories', [
                'attendance'    => 90,
                'performance'   => 85,
                'teamwork'      => 80,
                'initiative'    => 75,
                'communication' => 70,
                'quality'       => 80,
            ])
            ->set('status', 'submitted')
            ->call('save');

        $eval = HrPerformanceEvaluation::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($eval);
        $this->assertEquals(80, $eval->overall_score);
    }

    /** @test */
    public function can_update_existing_evaluation(): void
    {
        $eval = HrPerformanceEvaluation::create([
            'company_id'      => $this->user->company_id,
            'employee_id'     => $this->employee->id,
            'evaluator_id'    => $this->user->id,
            'period'          => '2026-Q1',
            'evaluation_date' => '2026-03-31',
            'categories'      => ['attendance' => 80, 'performance' => 80, 'teamwork' => 80, 'initiative' => 80, 'communication' => 80, 'quality' => 80],
            'overall_score'   => 80,
            'status'          => 'draft',
        ]);

        Livewire::test('App\Livewire\HR\EvaluationForm', ['evaluation' => $eval])
            ->set('status', 'completed')
            ->set('strengths', 'Excelente actitud y puntualidad.')
            ->call('save');

        $this->assertEquals('completed', $eval->fresh()->status);
    }
}
