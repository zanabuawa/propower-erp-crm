<?php

namespace Tests\Feature\Customers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerTest extends TestCase
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
    public function can_create_customer(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', 'Empresa Azteca S.A.')
            ->set('status', 'active')
            ->set('credit_limit', '50000')
            ->set('payment_terms', '30')
            ->set('country', 'México')
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'name'       => 'Empresa Azteca S.A.',
            'status'     => 'active',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', 'Cliente')
            ->set('status', 'vip')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function email_must_be_valid_format(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', 'Cliente')
            ->set('emails', [['id' => null, 'email' => 'no-es-email', 'type' => 'work', 'is_primary' => true]])
            ->call('save')
            ->assertHasErrors(['emails.0.email']);
    }

    /** @test */
    public function credit_limit_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', 'Cliente')
            ->set('credit_limit', '-100')
            ->call('save')
            ->assertHasErrors(['credit_limit']);
    }

    /** @test */
    public function can_update_existing_customer(): void
    {
        $customer = Customer::create([
            'company_id'    => $this->user->company_id,
            'name'          => 'Cliente Viejo',
            'status'        => 'prospect',
            'credit_limit'  => 0,
            'payment_terms' => 0,
        ]);

        Livewire::test('App\Livewire\Customers\CustomerForm', ['customer' => $customer])
            ->set('name', 'Cliente Actualizado')
            ->set('status', 'active')
            ->set('credit_limit', '100000')
            ->call('save');

        $this->assertEquals('Cliente Actualizado', $customer->fresh()->name);
        $this->assertEquals('active', $customer->fresh()->status);
    }

    /** @test */
    public function phones_are_saved_with_customer(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', 'Cliente con Teléfono')
            ->set('status', 'active')
            ->set('credit_limit', '0')
            ->set('payment_terms', '0')
            ->set('country', 'México')
            ->set('phones', [
                ['id' => null, 'number' => '5551234567', 'type' => 'mobile', 'is_primary' => true],
            ])
            ->call('save');

        $customer = Customer::where('name', 'Cliente con Teléfono')->first();
        $this->assertNotNull($customer);
        $this->assertCount(1, $customer->phones);
        $this->assertEquals('5551234567', $customer->phones->first()->number);
    }

    /** @test */
    public function contacts_are_saved_with_customer(): void
    {
        Livewire::test('App\Livewire\Customers\CustomerForm')
            ->set('name', 'Cliente con Contacto')
            ->set('status', 'active')
            ->set('credit_limit', '0')
            ->set('payment_terms', '0')
            ->set('country', 'México')
            ->set('contacts', [[
                'id'               => null,
                'first_name'       => 'Juan',
                'alias'            => '',
                'paternal_surname' => 'García',
                'maternal_surname' => '',
                'position'         => 'Director',
                'phone'            => '5559876543',
                'email'            => 'juan@empresa.com',
                'is_primary'       => true,
                'description'      => '',
            ]])
            ->call('save');

        $customer = Customer::where('name', 'Cliente con Contacto')->first();
        $this->assertNotNull($customer);
        $this->assertCount(1, $customer->contacts);
        $this->assertEquals('Juan', $customer->contacts->first()->first_name);
    }

    /** @test */
    public function can_add_and_remove_phones(): void
    {
        $component = Livewire::test('App\Livewire\Customers\CustomerForm');
        $initial = count($component->get('phones'));

        $component->call('addPhone');
        $this->assertCount($initial + 1, $component->get('phones'));

        $component->call('removePhone', 0);
        $this->assertCount($initial, $component->get('phones'));
    }
}
