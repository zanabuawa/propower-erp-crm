<?php

namespace Tests\Feature\Purchases;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PurchaseOrder $order;

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
        $supplier = Supplier::create([
            'company_id' => $company->id,
            'name'       => 'Proveedor Test S.A.',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->order = PurchaseOrder::create([
            'company_id'  => $company->id,
            'supplier_id' => $supplier->id,
            'created_by'  => $this->user->id,
            'folio'       => 'OC-000001',
            'status'      => 'draft',
            'currency'    => 'MXN',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function mark_as_sent_changes_status(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderShow', ['order' => $this->order])
            ->call('markAsSent');

        $this->assertEquals('sent', $this->order->fresh()->status);
    }

    /** @test */
    public function mark_as_waiting_delivery_changes_status(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderShow', ['order' => $this->order])
            ->call('markAsWaitingDelivery');

        $this->assertEquals('waiting_delivery', $this->order->fresh()->status);
    }

    /** @test */
    public function cancel_changes_status_to_cancelled(): void
    {
        Livewire::test('App\Livewire\Purchases\OrderShow', ['order' => $this->order])
            ->call('cancel');

        $this->assertEquals('cancelled', $this->order->fresh()->status);
    }
}
