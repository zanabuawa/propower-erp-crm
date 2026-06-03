<?php

namespace Tests\Feature\Tenders;

use App\Models\Company;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteVisitTest extends TestCase
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
    public function can_create_site_visit(): void
    {
        Livewire::test('App\Livewire\Tenders\SiteVisitForm')
            ->set('visit_date', '2026-06-15')
            ->set('visit_type', 'supervision')
            ->set('purpose', 'Verificar avance de obra civil en planta norte.')
            ->set('status', 'programada')
            ->call('save');

        $this->assertDatabaseHas('site_visits', [
            'visit_type' => 'supervision',
            'status'     => 'programada',
        ]);
    }

    /** @test */
    public function visit_date_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\SiteVisitForm')
            ->set('visit_date', '')
            ->set('purpose', 'Verificar avance.')
            ->call('save')
            ->assertHasErrors(['visit_date' => 'required']);
    }

    /** @test */
    public function purpose_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\SiteVisitForm')
            ->set('visit_date', '2026-06-15')
            ->set('purpose', '')
            ->call('save')
            ->assertHasErrors(['purpose' => 'required']);
    }

    /** @test */
    public function visit_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Tenders\SiteVisitForm')
            ->set('visit_date', '2026-06-15')
            ->set('purpose', 'Visita de prueba.')
            ->set('visit_type', 'evaluacion')
            ->call('save')
            ->assertHasErrors(['visit_type']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Tenders\SiteVisitForm')
            ->set('visit_date', '2026-06-15')
            ->set('purpose', 'Visita de prueba.')
            ->set('status', 'pendiente')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function can_update_existing_site_visit(): void
    {
        $visit = SiteVisit::create([
            'company_id' => $this->user->company_id,
            'created_by' => $this->user->id,
            'visit_date' => '2026-06-15',
            'visit_type' => 'supervision',
            'purpose'    => 'Verificación inicial.',
            'status'     => 'programada',
            'attendees'  => [],
            'photos'     => [],
        ]);

        Livewire::test('App\Livewire\Tenders\SiteVisitForm', ['siteVisit' => $visit])
            ->set('status', 'realizada')
            ->set('report', 'Obra al 60% de avance, sin incidencias.')
            ->call('save');

        $this->assertEquals('realizada', $visit->fresh()->status);
    }
}
