<?php

namespace Tests\Feature\Purchases;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected FinanceAccount $account;
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
        $this->account = FinanceAccount::create([
            'company_id'      => $company->id,
            'code'            => 'BBVA-001',
            'name'            => 'BBVA Cuenta',
            'type'            => 'banco',
            'currency'        => 'MXN',
            'opening_balance' => 100000,
            'current_balance' => 100000,
            'is_active'       => true,
        ]);
        $this->product = Product::create([
            'company_id'     => $company->id,
            'name'           => 'Cable Eléctrico',
            'type'           => 'product',
            'sale_price'     => 50,
            'purchase_price' => 30,
            'profit_margin'  => 40,
            'is_active'      => true,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function warehouse_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\GoodsReceiptForm')
            ->set('warehouse_id', null)
            ->set('reception_type', 'purchase')
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['warehouse_id']);
    }

    /** @test */
    public function items_are_required(): void
    {
        Livewire::test('App\Livewire\Purchases\GoodsReceiptForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }

    /** @test */
    public function finance_account_required_for_purchase_type(): void
    {
        Livewire::test('App\Livewire\Purchases\GoodsReceiptForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('reception_type', 'purchase')
            ->set('financeAccountId', null)
            ->set('items', [[
                'product_id'          => $this->product->id,
                'product_name'        => 'Cable',
                'sku'                 => '',
                'quantity'            => 10,
                'quantity_ordered'    => 10,
                'purchase_price'      => 30,
                'prev_purchase_price' => 30,
                'profit_margin'       => 40,
                'received'            => true,
                'notes'               => '',
                'quantity_rejected'   => 0,
                'rejection_reason'    => '',
            ]])
            ->call('save')
            ->assertHasErrors(['financeAccountId']);
    }

    /** @test */
    public function reception_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Purchases\GoodsReceiptForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('reception_type', 'rechazo')
            ->call('save')
            ->assertHasErrors(['reception_type']);
    }

    /** @test */
    public function operating_expenses_must_be_non_negative(): void
    {
        Livewire::test('App\Livewire\Purchases\GoodsReceiptForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('operating_expenses', -500)
            ->call('save')
            ->assertHasErrors(['operating_expenses']);
    }

    /** @test */
    public function item_quantity_must_be_positive(): void
    {
        Livewire::test('App\Livewire\Purchases\GoodsReceiptForm')
            ->set('warehouse_id', $this->warehouse->id)
            ->set('financeAccountId', $this->account->id)
            ->set('reception_type', 'purchase')
            ->set('items', [[
                'product_id'          => $this->product->id,
                'product_name'        => 'Cable',
                'sku'                 => '',
                'quantity'            => 0,
                'quantity_ordered'    => 10,
                'purchase_price'      => 30,
                'prev_purchase_price' => 30,
                'profit_margin'       => 40,
                'received'            => true,
                'notes'               => '',
                'quantity_rejected'   => 0,
                'rejection_reason'    => '',
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);
    }
}
