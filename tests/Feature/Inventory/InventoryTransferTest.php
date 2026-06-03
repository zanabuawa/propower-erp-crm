<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouseA;
    protected Warehouse $warehouseB;
    protected Warehouse $transitWarehouse;
    protected Product $product;

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
        $branchA = Branch::create([
            'company_id' => $company->id, 'name' => 'Sucursal A', 'is_active' => true,
        ]);
        $branchB = Branch::create([
            'company_id' => $company->id, 'name' => 'Sucursal B', 'is_active' => true,
        ]);
        $this->warehouseA = Warehouse::create([
            'company_id'   => $company->id,
            'branch_id'    => $branchA->id,
            'name'         => 'Almacén A',
            'is_active'    => true,
            'is_defective' => false,
            'is_transit'   => false,
        ]);
        $this->warehouseB = Warehouse::create([
            'company_id'   => $company->id,
            'branch_id'    => $branchB->id,
            'name'         => 'Almacén B',
            'is_active'    => true,
            'is_defective' => false,
            'is_transit'   => false,
        ]);
        $this->transitWarehouse = Warehouse::create([
            'company_id'   => $company->id,
            'branch_id'    => $branchA->id,
            'name'         => 'Almacén Tránsito',
            'is_active'    => true,
            'is_defective' => false,
            'is_transit'   => true,
        ]);
        $this->product = Product::create([
            'company_id'     => $company->id,
            'name'           => 'Panel Solar',
            'type'           => 'product',
            'sale_price'     => 5000,
            'purchase_price' => 3000,
            'profit_margin'  => 40,
            'is_active'      => true,
        ]);
        Stock::create([
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouseA->id,
            'quantity'     => 100,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function warehouse_is_required(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', null)
            ->set('warehouse_destination_id', $this->warehouseB->id)
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'item_id'          => null,
                'product_id'       => $this->product->id,
                'product_name'     => 'Panel Solar',
                'sku'              => '',
                'quantity'         => 10,
                'stock_origen'     => 100,
                'dispatched_quantity' => 10,
                'received_quantity'   => null,
                'received_at'         => '',
                'is_late_addition'    => false,
                'added_at'            => null,
            ]])
            ->call('save')
            ->assertHasErrors(['warehouse_id']);
    }

    /** @test */
    public function destination_warehouse_must_differ_from_origin(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', $this->warehouseA->id)
            ->set('warehouse_destination_id', $this->warehouseA->id)
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'item_id'             => null,
                'product_id'          => $this->product->id,
                'product_name'        => 'Panel Solar',
                'sku'                 => '',
                'quantity'            => 10,
                'stock_origen'        => 100,
                'dispatched_quantity' => 10,
                'received_quantity'   => null,
                'received_at'         => '',
                'is_late_addition'    => false,
                'added_at'            => null,
            ]])
            ->call('save')
            ->assertHasErrors(['warehouse_destination_id']);
    }

    /** @test */
    public function items_are_required(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', $this->warehouseA->id)
            ->set('warehouse_destination_id', $this->warehouseB->id)
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }

    /** @test */
    public function item_quantity_must_be_positive(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', $this->warehouseA->id)
            ->set('warehouse_destination_id', $this->warehouseB->id)
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'item_id'             => null,
                'product_id'          => $this->product->id,
                'product_name'        => 'Panel Solar',
                'sku'                 => '',
                'quantity'            => 0,
                'stock_origen'        => 100,
                'dispatched_quantity' => 0,
                'received_quantity'   => null,
                'received_at'         => '',
                'is_late_addition'    => false,
                'added_at'            => null,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);
    }

    /** @test */
    public function moved_at_is_required(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', $this->warehouseA->id)
            ->set('warehouse_destination_id', $this->warehouseB->id)
            ->set('moved_at', '')
            ->call('save')
            ->assertHasErrors(['moved_at']);
    }

    /** @test */
    public function can_create_transfer_request(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', $this->warehouseA->id)
            ->set('warehouse_destination_id', $this->warehouseB->id)
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'item_id'             => null,
                'product_id'          => $this->product->id,
                'product_name'        => 'Panel Solar',
                'sku'                 => '',
                'quantity'            => 5,
                'stock_origen'        => 100,
                'dispatched_quantity' => 5,
                'received_quantity'   => null,
                'received_at'         => '',
                'is_late_addition'    => false,
                'added_at'            => null,
            ]])
            ->call('save');

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id'             => $this->warehouseA->id,
            'warehouse_destination_id' => $this->warehouseB->id,
            'type'                     => 'transfer',
            'status'                   => 'requested',
        ]);
    }

    /** @test */
    public function transfer_folio_starts_with_mov_t(): void
    {
        Livewire::test('App\Livewire\Inventory\InventoryTransferForm')
            ->set('warehouse_id', $this->warehouseA->id)
            ->set('warehouse_destination_id', $this->warehouseB->id)
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'item_id'             => null,
                'product_id'          => $this->product->id,
                'product_name'        => 'Panel Solar',
                'sku'                 => '',
                'quantity'            => 3,
                'stock_origen'        => 100,
                'dispatched_quantity' => 3,
                'received_quantity'   => null,
                'received_at'         => '',
                'is_late_addition'    => false,
                'added_at'            => null,
            ]])
            ->call('save');

        $transfer = StockMovement::first();
        $this->assertNotNull($transfer);
        $this->assertStringStartsWith('MOV-T-', $transfer->folio);
    }
}
