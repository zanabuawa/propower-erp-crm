<?php

namespace Tests\Feature\Sales;

use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesOrderShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SaleOrder $order;

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
        $this->order = SaleOrder::create([
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
            'created_by'  => $this->user->id,
            'folio'          => 'PV-000001',
            'currency'       => 'MXN',
            'status'         => 'confirmed',
            'payment_method' => 'cash',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function cancel_changes_order_status_to_cancelled(): void
    {
        Livewire::test('App\Livewire\Sales\OrderShow', ['order' => $this->order])
            ->call('cancel');

        $this->assertEquals('cancelled', $this->order->fresh()->status);
    }
}
