<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\CrmTicket;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmTicketTest extends TestCase
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
    public function can_create_ticket(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketForm')
            ->set('subject', 'Falla en inversor solar')
            ->set('type', 'warranty')
            ->set('priority', 'high')
            ->set('customer_id', $this->customer->id)
            ->call('save');

        $this->assertDatabaseHas('crm_tickets', [
            'subject'     => 'Falla en inversor solar',
            'type'        => 'warranty',
            'status'      => 'open',
            'company_id'  => $this->user->company_id,
        ]);
    }

    /** @test */
    public function can_create_internal_company_ticket(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketForm')
            ->set('ticket_scope', 'internal')
            ->set('subject', 'Falla en aire acondicionado de oficina')
            ->set('type', 'internal')
            ->set('priority', 'medium')
            ->set('customer_id', $this->customer->id)
            ->call('save');

        $this->assertDatabaseHas('crm_tickets', [
            'subject'         => 'Falla en aire acondicionado de oficina',
            'type'            => 'internal',
            'customer_id'     => null,
            'sale_order_id'   => null,
            'sale_invoice_id' => null,
            'company_id'      => $this->user->company_id,
        ]);
    }

    /** @test */
    public function subject_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketForm')
            ->set('subject', '')
            ->call('save')
            ->assertHasErrors(['subject' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketForm')
            ->set('subject', 'Ticket')
            ->set('type', 'queja_legal')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function priority_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketForm')
            ->set('subject', 'Ticket')
            ->set('priority', 'extremo')
            ->call('save')
            ->assertHasErrors(['priority']);
    }

    /** @test */
    public function folio_is_generated_with_tkt_prefix(): void
    {
        Livewire::test('App\Livewire\Sales\CrmTicketForm')
            ->set('subject', 'Consulta técnica')
            ->set('type', 'inquiry')
            ->set('priority', 'low')
            ->call('save');

        $ticket = CrmTicket::first();
        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('TKT-', $ticket->folio);
    }

    /** @test */
    public function can_update_existing_ticket(): void
    {
        $ticket = CrmTicket::create([
            'company_id' => $this->user->company_id,
            'created_by' => $this->user->id,
            'folio'      => 'TKT-00001',
            'subject'    => 'Ticket Original',
            'type'       => 'support',
            'priority'   => 'medium',
            'status'     => 'open',
        ]);

        Livewire::test('App\Livewire\Sales\CrmTicketForm', ['ticket' => $ticket])
            ->set('subject', 'Ticket Actualizado')
            ->set('priority', 'urgent')
            ->call('save');

        $this->assertEquals('Ticket Actualizado', $ticket->fresh()->subject);
        $this->assertEquals('urgent', $ticket->fresh()->priority);
    }
}
