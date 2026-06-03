<?php

namespace Tests\Feature\Purchases;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PurchaseOrder $order;
    protected Warehouse $warehouse;
    protected FinanceAccount $account;

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
            'company_id' => $company->id,
            'name'       => 'Sucursal Principal',
            'code'       => 'SUC-01',
            'is_active'  => true,
        ]);
        $this->warehouse = Warehouse::create([
            'company_id'   => $company->id,
            'branch_id'    => $branch->id,
            'name'         => 'Almacén Central',
            'is_active'    => true,
            'is_defective' => false,
            'is_transit'   => false,
        ]);
        $this->account = FinanceAccount::create([
            'company_id' => $company->id,
            'code'       => 'CAJA-01',
            'name'       => 'Caja General',
            'type'       => 'caja',
            'currency'   => 'MXN',
            'is_active'  => true,
        ]);
        $supplier = Supplier::create([
            'company_id' => $company->id,
            'name'       => 'Proveedor Eléctrico S.A.',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->order = PurchaseOrder::create([
            'company_id'  => $company->id,
            'supplier_id' => $supplier->id,
            'created_by'  => $this->user->id,
            'folio'       => 'OC-000001',
            'status'      => 'sent',
            'currency'    => 'MXN',
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $this->order->id,
            'description'       => 'Cable eléctrico 12AWG',
            'quantity'          => 100,
            'quantity_received' => 0,
            'unit_price'        => 15.00,
            'tax_rate'          => 16,
            'subtotal'          => 1500,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_receipt(): void
    {
        Livewire::test('App\Livewire\Purchases\ReceiptForm', ['order' => $this->order])
            ->set('warehouse_id', $this->warehouse->id)
            ->set('financeAccountId', $this->account->id)
            ->call('save');

        $this->assertDatabaseHas('purchase_receipts', [
            'purchase_order_id' => $this->order->id,
            'status'            => 'completed',
            'reception_type'    => 'purchase',
        ]);
    }

    /** @test */
    public function warehouse_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\ReceiptForm', ['order' => $this->order])
            ->set('warehouse_id', null)
            ->call('save')
            ->assertHasErrors(['warehouse_id' => 'required']);
    }

    /** @test */
    public function reception_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Purchases\ReceiptForm', ['order' => $this->order])
            ->set('warehouse_id', $this->warehouse->id)
            ->set('reception_type', 'entrega')
            ->call('save')
            ->assertHasErrors(['reception_type']);
    }

    /** @test */
    public function finance_account_required_for_purchase_type(): void
    {
        Livewire::test('App\Livewire\Purchases\ReceiptForm', ['order' => $this->order])
            ->set('warehouse_id', $this->warehouse->id)
            ->set('reception_type', 'purchase')
            ->set('financeAccountId', null)
            ->call('save')
            ->assertHasErrors(['financeAccountId' => 'required']);
    }

    /** @test */
    public function folio_has_rec_prefix(): void
    {
        Livewire::test('App\Livewire\Purchases\ReceiptForm', ['order' => $this->order])
            ->set('warehouse_id', $this->warehouse->id)
            ->set('financeAccountId', $this->account->id)
            ->call('save');

        $receipt = PurchaseReceipt::first();
        $this->assertNotNull($receipt);
        $this->assertStringStartsWith('REC-', $receipt->folio);
    }
}
