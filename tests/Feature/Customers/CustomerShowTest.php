<?php

namespace Tests\Feature\Customers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerShowTest extends TestCase
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
            'company_id' => $company->id,
            'name'       => 'Constructora Norte S.A.',
            'status'     => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function save_note_creates_customer_note(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerShow', ['customer' => $this->customer])
            ->set('noteTitle', 'Reunión inicial')
            ->set('noteType', 'meeting')
            ->set('noteBody', 'Se presentó propuesta de servicios.')
            ->call('saveNote');

        $this->assertDatabaseHas('customer_notes', [
            'customer_id' => $this->customer->id,
            'title'       => 'Reunión inicial',
            'type'        => 'meeting',
        ]);
    }

    /** @test */
    public function save_note_requires_title(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerShow', ['customer' => $this->customer])
            ->set('noteTitle', '')
            ->set('noteType', 'note')
            ->call('saveNote')
            ->assertHasErrors(['noteTitle' => 'required']);
    }

    /** @test */
    public function delete_note_removes_it(): void
    {
        $note = CustomerNote::create([
            'customer_id' => $this->customer->id,
            'user_id'     => $this->user->id,
            'type'        => 'call',
            'title'       => 'Llamada de seguimiento',
            'noted_at'    => now(),
        ]);

        Livewire::test('App\Livewire\Customers\CustomerShow', ['customer' => $this->customer])
            ->call('deleteNote', $note->id);

        $this->assertDatabaseMissing('customer_notes', ['id' => $note->id]);
    }
}
