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

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
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
        $branch = Branch::create([
            'company_id' => $company->id, 'name' => 'Matriz', 'is_active' => true,
        ]);
        $this->warehouse = Warehouse::create([
            'company_id'  => $company->id,
            'branch_id'   => $branch->id,
            'name'        => 'Almacén Principal',
            'is_active'   => true,
            'is_defective'=> false,
            'is_transit'  => false,
        ]);
        $this->product = Product::create([
            'company_id'     => $company->id,
            'name'           => 'Tornillo M6',
            'type'           => 'product',
            'sale_price'     => 2,
            'purchase_price' => 1,
            'profit_margin'  => 50,
            'is_active'      => true,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function warehouse_is_required(): void
    {
        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', null)
            ->call('save')
            ->assertHasErrors(['warehouse_id']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'robo')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function items_are_required(): void
    {
        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'entry')
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }

    /** @test */
    public function item_quantity_must_be_positive(): void
    {
        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'entry')
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'product_id'   => $this->product->id,
                'product_name' => 'Tornillo M6',
                'sku'          => '',
                'quantity'     => 0,
                'unit_price'   => 1,
                'expiry_date'  => '',
                'lot_notes'    => '',
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);
    }

    /** @test */
    public function adjustment_requires_reason(): void
    {
        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'adjustment')
            ->set('adjustment_reason', '')
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'product_id'   => $this->product->id,
                'product_name' => 'Tornillo M6',
                'sku'          => '',
                'quantity'     => 100,
                'unit_price'   => 1,
                'expiry_date'  => '',
                'lot_notes'    => '',
            ]])
            ->call('save')
            ->assertHasErrors(['adjustment_reason']);
    }

    /** @test */
    public function moved_at_is_required(): void
    {
        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'entry')
            ->set('moved_at', '')
            ->call('save')
            ->assertHasErrors(['moved_at']);
    }

    /** @test */
    public function can_create_entry_movement(): void
    {
        // Set up existing stock so FIFO has context
        Stock::create([
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 0,
        ]);

        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'entry')
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'product_id'   => $this->product->id,
                'product_name' => 'Tornillo M6',
                'sku'          => '',
                'quantity'     => 500,
                'unit_price'   => 1,
                'expiry_date'  => '',
                'lot_notes'    => '',
            ]])
            ->call('save');

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'type'         => 'entry',
            'status'       => 'confirmed',
            'company_id'   => $this->user->company_id,
        ]);
    }

    /** @test */
    public function folio_has_type_prefix(): void
    {
        Stock::create([
            'product_id'   => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 0,
        ]);

        Livewire::test('App\Livewire\Inventory\StockMovementForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('type', 'entry')
            ->set('moved_at', now()->format('Y-m-d\TH:i'))
            ->set('items', [[
                'product_id'   => $this->product->id,
                'product_name' => 'Tornillo M6',
                'sku'          => '',
                'quantity'     => 100,
                'unit_price'   => 1,
                'expiry_date'  => '',
                'lot_notes'    => '',
            ]])
            ->call('save');

        $movement = StockMovement::first();
        $this->assertNotNull($movement);
        $this->assertStringContainsString('MOV-', $movement->folio);
    }
}
