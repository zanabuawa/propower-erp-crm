<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\CrmTicket;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmTicketShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CrmTicket $ticket;

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
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Cliente Test']);
        $this->ticket = CrmTicket::create([
            'company_id'  => $company->id,
            'created_by'  => $this->user->id,
            'assigned_to' => $this->user->id,
            'customer_id' => $customer->id,
            'folio'       => 'TKT-0001',
            'subject'     => 'Falla en equipo instalado',
            'description' => 'El tablero eléctrico no enciende.',
            'type'        => 'soporte',
            'priority'    => 'high',
            'status'      => 'open',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function update_status_changes_ticket_status(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketShow', ['ticket' => $this->ticket])
            ->set('newStatus', 'in_progress')
            ->call('updateStatus');

        $this->assertEquals('in_progress', $this->ticket->fresh()->status);
    }

    /** @test */
    public function resolve_ticket_sets_resolved_at(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketShow', ['ticket' => $this->ticket])
            ->set('newStatus', 'resolved')
            ->call('updateStatus');

        $fresh = $this->ticket->fresh();
        $this->assertEquals('resolved', $fresh->status);
        $this->assertNotNull($fresh->resolved_at);
    }
}
