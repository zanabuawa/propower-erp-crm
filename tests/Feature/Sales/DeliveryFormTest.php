<?php

namespace Tests\Feature\Sales;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryFormTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
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
        $branch = Branch::create([
            'company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true,
        ]);
        $this->warehouse = Warehouse::create([
            'company_id' => $company->id,
            'branch_id'  => $branch->id,
            'name'       => 'Almacén Central',
            'code'       => 'ALM-001',
            'type'       => 'standard',
            'is_active'  => true,
        ]);
        $customer = Customer::create([
            'company_id'    => $company->id,
            'name'          => 'Cliente Test',
            'status'        => 'active',
            'credit_limit'  => 0,
            'payment_terms' => 0,
        ]);
        $this->order = SaleOrder::create([
            'company_id'  => $company->id,
            'customer_id' => $customer->id,
            'created_by'  => $this->user->id,
            'folio'       => 'OV-000001',
            'status'      => 'confirmed',
            'currency'    => 'MXN',
            'subtotal'    => 5000,
            'tax'         => 800,
            'total'       => 5800,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function warehouse_is_required(): void
    {
        Livewire::test('App\Livewire\Sales\DeliveryForm', ['order' => $this->order])
            ->set('warehouse_id', null)
            ->set('items', [[
                'order_item_id'       => 1,
                'product_id'          => 1,
                'product_name'        => 'Producto',
                'quantity_pending'    => 5,
                'quantity_to_deliver' => 5,
                'warehouse_id'        => null,
                'lot_lines'           => [],
                'lot_available'       => 0,
                'include'             => true,
            ]])
            ->call('save')
            ->assertHasErrors(['warehouse_id' => 'required']);
    }

    /** @test */
    public function reason_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Sales\DeliveryForm', ['order' => $this->order])
            ->set('warehouse_id', $this->warehouse->id)
            ->set('reason', 'otro_invalido')
            ->call('save')
            ->assertHasErrors(['reason']);
    }

    /** @test */
    public function items_are_required(): void
    {
        Livewire::test('App\Livewire\Sales\DeliveryForm', ['order' => $this->order])
            ->set('warehouse_id', $this->warehouse->id)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }
}
