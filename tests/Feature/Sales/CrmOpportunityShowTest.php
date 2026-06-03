<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\CrmActivity;
use App\Models\SalesOpportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmOpportunityShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SalesOpportunity $opportunity;

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
        $this->opportunity = SalesOpportunity::create([
            'company_id'  => $company->id,
            'assigned_to' => $this->user->id,
            'title'       => 'Proyecto Eléctrico Monterrey',
            'stage'       => 'qualification',
            'probability' => 10,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function move_stage_updates_opportunity_stage(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityShow', ['opportunity' => $this->opportunity])
            ->call('moveStage', 'proposal');

        $this->assertEquals('proposal', $this->opportunity->fresh()->stage);
        $this->assertEquals(30, $this->opportunity->fresh()->probability);
    }

    /** @test */
    public function confirm_lost_marks_opportunity_as_lost(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityShow', ['opportunity' => $this->opportunity])
            ->set('lostReason', 'El cliente eligió a otro proveedor.')
            ->call('confirmLost');

        $fresh = $this->opportunity->fresh();
        $this->assertEquals('lost', $fresh->stage);
        $this->assertNotNull($fresh->lost_at);
    }

    /** @test */
    public function save_activity_creates_crm_activity(): void
    {
        Livewire::test('App\Livewire\Sales\CrmOpportunityShow', ['opportunity' => $this->opportunity])
            ->set('activityType', 'call')
            ->set('activityTitle', 'Llamada de seguimiento')
            ->set('activityScheduled', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('saveActivity');

        $this->assertDatabaseHas('crm_activities', [
            'opportunity_id' => $this->opportunity->id,
            'type'           => 'call',
            'title'          => 'Llamada de seguimiento',
        ]);
    }

    /** @test */
    public function complete_activity_marks_it_as_completed(): void
    {
        $activity = CrmActivity::create([
            'company_id'     => $this->opportunity->company_id,
            'user_id'        => $this->user->id,
            'opportunity_id' => $this->opportunity->id,
            'type'           => 'email',
            'title'          => 'Envío de propuesta',
            'status'         => 'pending',
        ]);

        Livewire::test('App\Livewire\Sales\CrmOpportunityShow', ['opportunity' => $this->opportunity])
            ->call('completeActivity', $activity->id);

        $this->assertEquals('completed', $activity->fresh()->status);
    }

    /** @test */
    public function delete_activity_removes_it(): void
    {
        $activity = CrmActivity::create([
            'company_id'     => $this->opportunity->company_id,
            'user_id'        => $this->user->id,
            'opportunity_id' => $this->opportunity->id,
            'type'           => 'meeting',
            'title'          => 'Reunión presencial',
            'status'         => 'pending',
        ]);

        Livewire::test('App\Livewire\Sales\CrmOpportunityShow', ['opportunity' => $this->opportunity])
            ->call('deleteActivity', $activity->id);

        $this->assertDatabaseMissing('crm_activities', ['id' => $activity->id]);
    }
}
