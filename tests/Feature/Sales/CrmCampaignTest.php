<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\CrmCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmCampaignTest extends TestCase
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
    public function can_create_campaign(): void
    {
        Livewire::test('App\Livewire\Sales\CrmCampaignForm')
            ->set('name', 'Campaña Verano 2026')
            ->set('type', 'email')
            ->set('status', 'draft')
            ->set('budget', '50000')
            ->call('save');

        $this->assertDatabaseHas('crm_campaigns', [
            'name'   => 'Campaña Verano 2026',
            'type'   => 'email',
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CrmCampaignForm')
            ->set('name', '')
            ->set('type', 'email')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\CrmCampaignForm')
            ->set('name', 'Campaña Inválida')
            ->set('type', 'tv')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\CrmCampaignForm')
            ->set('name', 'Campaña Prueba')
            ->set('type', 'whatsapp')
            ->set('status', 'pendiente')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function end_date_must_be_after_start_date(): void
    {
        Livewire::test('App\Livewire\Sales\CrmCampaignForm')
            ->set('name', 'Campaña Fechas')
            ->set('type', 'social_media')
            ->set('start_at', '2026-06-30')
            ->set('end_at', '2026-06-01')
            ->call('save')
            ->assertHasErrors(['end_at']);
    }

    /** @test */
    public function folio_is_generated_with_cam_prefix(): void
    {
        Livewire::test('App\Livewire\Sales\CrmCampaignForm')
            ->set('name', 'Lanzamiento Producto Q3')
            ->set('type', 'event')
            ->set('status', 'draft')
            ->call('save');

        $campaign = CrmCampaign::where('name', 'Lanzamiento Producto Q3')->first();
        $this->assertNotNull($campaign);
        $this->assertStringStartsWith('CAM-', $campaign->folio);
    }

    /** @test */
    public function can_update_existing_campaign(): void
    {
        $campaign = CrmCampaign::create([
            'company_id' => $this->user->company_id,
            'created_by' => $this->user->id,
            'folio'      => 'CAM-00001',
            'name'       => 'Campaña Original',
            'type'       => 'email',
            'status'     => 'draft',
        ]);

        Livewire::test('App\Livewire\Sales\CrmCampaignForm', ['campaign' => $campaign])
            ->set('status', 'active')
            ->set('budget', '75000')
            ->call('save');

        $this->assertEquals('active', $campaign->fresh()->status);
    }
}
