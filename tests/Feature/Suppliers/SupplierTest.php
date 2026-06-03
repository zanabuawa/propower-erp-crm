<?php

namespace Tests\Feature\Suppliers;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierTest extends TestCase
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
    public function can_create_supplier(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierForm')
            ->set('name', 'Distribuidora Norte S.A.')
            ->set('type', 'company')
            ->set('status', 'active')
            ->set('credit_limit', '100000')
            ->set('payment_terms', '30')
            ->set('country', 'México')
            ->call('save');

        $this->assertDatabaseHas('suppliers', [
            'name'       => 'Distribuidora Norte S.A.',
            'type'       => 'company',
            'status'     => 'active',
            'company_id' => $this->user->company_id,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierForm')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function type_must_be_person_or_company(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierForm')
            ->set('name', 'Proveedor')
            ->set('type', 'government')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function status_must_be_active_or_inactive(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierForm')
            ->set('name', 'Proveedor')
            ->set('status', 'banned')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function email_must_be_valid_format(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierForm')
            ->set('name', 'Proveedor')
            ->set('emails', [['id' => null, 'email' => 'no-valido', 'type' => 'work', 'is_primary' => true]])
            ->call('save')
            ->assertHasErrors(['emails.0.email']);
    }

    /** @test */
    public function can_update_existing_supplier(): void
    {
        $supplier = Supplier::create([
            'company_id'    => $this->user->company_id,
            'name'          => 'Proveedor Viejo',
            'type'          => 'company',
            'status'        => 'active',
            'credit_limit'  => 0,
            'payment_terms' => 0,
        ]);

        Livewire::test('App\Livewire\Suppliers\SupplierForm', ['supplier' => $supplier])
            ->set('name', 'Proveedor Actualizado')
            ->set('credit_limit', '250000')
            ->call('save');

        $this->assertEquals('Proveedor Actualizado', $supplier->fresh()->name);
    }

    /** @test */
    public function can_add_and_remove_bank_accounts(): void
    {
        $component = Livewire::test('App\Livewire\Suppliers\SupplierForm');
        $initial = count($component->get('bankAccounts'));

        $component->call('addBankAccount');
        $this->assertCount($initial + 1, $component->get('bankAccounts'));

        $component->call('removeBankAccount', 0);
        $this->assertCount($initial, $component->get('bankAccounts'));
    }

    /** @test */
    public function service_type_must_be_valid_if_provided(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierForm')
            ->set('name', 'Proveedor')
            ->set('service_type', 'cleaning')
            ->call('save')
            ->assertHasErrors(['service_type']);
    }
}
