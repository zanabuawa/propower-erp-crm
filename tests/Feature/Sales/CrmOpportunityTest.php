<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\SalesOpportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmOpportunityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

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
        $this->customer = Customer::create([
            'company_id'    => $company->id,
            'name'          => 'Cliente Test',
            'status'        => 'active',
            'credit_limit'  => 0,
            'payment_terms' => 0,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_opportunity(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('title', 'Proyecto Energía Solar Monterrey')
            ->set('stage', 'qualification')
            ->set('probability', '20')
            ->set('estimated_value', '500000')
            ->set('customer_id', $this->customer->id)
            ->call('save');

        $this->assertDatabaseHas('sales_opportunities', [
            'title'      => 'Proyecto Energía Solar Monterrey',
            'stage'      => 'qualification',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function title_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }

    /** @test */
    public function stage_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('title', 'Oportunidad')
            ->set('stage', 'pendiente')
            ->call('save')
            ->assertHasErrors(['stage']);
    }

    /** @test */
    public function probability_must_be_between_0_and_100(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('title', 'Oportunidad')
            ->set('probability', '150')
            ->call('save')
            ->assertHasErrors(['probability']);
    }

    /** @test */
    public function estimated_value_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('title', 'Oportunidad')
            ->set('estimated_value', '-1000')
            ->call('save')
            ->assertHasErrors(['estimated_value']);
    }

    /** @test */
    public function stage_update_changes_probability(): void
    {
        $component = Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('stage', 'won');

        $this->assertEquals('100', $component->get('probability'));
    }

    /** @test */
    public function can_update_existing_opportunity(): void
    {
        $opp = SalesOpportunity::create([
            'company_id'     => $this->user->company_id,
            'title'          => 'Oportunidad Original',
            'stage'          => 'qualification',
            'probability'    => 20,
            'estimated_value'=> 100000,
            'assigned_to'    => $this->user->id,
        ]);

        Livewire::test('App\Livewire\Sales\CrmOpportunityForm', ['opportunity' => $opp])
            ->set('title', 'Oportunidad Actualizada')
            ->set('stage', 'proposal')
            ->call('save');

        $this->assertEquals('Oportunidad Actualizada', $opp->fresh()->title);
        $this->assertEquals('proposal', $opp->fresh()->stage);
    }

    /** @test */
    public function won_stage_records_won_at(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityForm')
            ->set('title', 'Oportunidad Ganada')
            ->set('stage', 'won')
            ->set('probability', '100')
            ->call('save');

        $opp = SalesOpportunity::where('title', 'Oportunidad Ganada')->first();
        $this->assertNotNull($opp);
        $this->assertNotNull($opp->won_at);
    }
}
