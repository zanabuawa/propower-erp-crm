<?php

namespace Tests\Feature\Customers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::create([
            'name' => 'Test Co', 'legal_name' => 'Test Co S.A.', 'rfc' => 'ABC123456789',
        ]);
        $this->user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'company_id' => $this->company->id,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_delete_customer(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name'       => 'Cliente Prueba S.A.',
        ]);

        Livewire::test('App\Livewire\Customers\CustomerIndex')
            ->call('confirmDelete', $customer->id)
            ->call('delete');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    /** @test */
    public function cancel_delete_keeps_customer(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name'       => 'Cliente Permanente S.A.',
        ]);

        Livewire::test('App\Livewire\Customers\CustomerIndex')
            ->call('confirmDelete', $customer->id)
            ->call('cancelDelete');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
