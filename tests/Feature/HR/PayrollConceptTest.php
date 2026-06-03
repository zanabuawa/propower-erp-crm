<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrPayrollConcept;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollConceptTest extends TestCase
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
    public function can_create_payroll_concept(): void
    {
        Livewire::test('App\Livewire\HR\PayrollConceptForm')
            ->set('name', 'Bono de Productividad')
            ->set('code', 'BON-PROD')
            ->set('type', 'perception')
            ->set('is_taxable', true)
            ->call('save');

        $this->assertDatabaseHas('hr_payroll_concepts', [
            'name' => 'Bono de Productividad',
            'type' => 'perception',
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\HR\PayrollConceptForm')
            ->set('name', '')
            ->set('type', 'perception')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\HR\PayrollConceptForm')
            ->set('name', 'Concepto Inválido')
            ->set('type', 'bonus')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function can_create_deduction_concept(): void
    {
        Livewire::test('App\Livewire\HR\PayrollConceptForm')
            ->set('name', 'Préstamo Caja de Ahorro')
            ->set('type', 'deduction')
            ->set('is_taxable', false)
            ->call('save');

        $this->assertDatabaseHas('hr_payroll_concepts', [
            'name' => 'Préstamo Caja de Ahorro',
            'type' => 'deduction',
        ]);
    }

    /** @test */
    public function can_update_existing_concept(): void
    {
        $concept = HrPayrollConcept::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Concepto Original',
            'type'       => 'perception',
            'is_taxable' => true,
            'is_active'  => true,
        ]);

        Livewire::test('App\Livewire\HR\PayrollConceptForm', ['payrollConcept' => $concept])
            ->set('name', 'Concepto Actualizado')
            ->set('is_active', false)
            ->call('save');

        $this->assertEquals('Concepto Actualizado', $concept->fresh()->name);
        $this->assertFalse($concept->fresh()->is_active);
    }
}
