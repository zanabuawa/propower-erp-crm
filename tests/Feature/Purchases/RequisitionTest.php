<?php

namespace Tests\Feature\Purchases;

use App\Models\Company;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RequisitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $company = Company::create([
            'name'       => 'Test Company',
            'legal_name' => 'Test Company S.A.',
            'rfc'        => 'ABC123456789',
        ]);

        $this->user = User::create([
            'name'       => 'Admin',
            'email'      => 'admin@example.com',
            'password'   => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->product = Product::create([
            'company_id'     => $company->id,
            'type'           => 'product',
            'name'           => 'Tornillo M8',
            'purchase_price' => '5',
            'profit_margin'  => '30',
            'sale_price'     => '7.14',
            'min_stock'      => '0',
            'max_stock'      => '0',
            'is_active'      => true,
        ]);

        $this->actingAs($this->user);
    }

    private function validItem(array $overrides = []): array
    {
        return array_merge([
            'product_id'  => null,
            'item_type'   => 'product',
            'description' => 'Material de prueba',
            'quantity'    => 10,
            'unit_price'  => 50,
            'unit'        => 'pz',
            'notes'       => '',
            'stock_info'  => null,
        ], $overrides);
    }

    /** @test */
    public function can_create_requisition(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Se requiere para mantenimiento preventivo')
            ->set('currency', 'MXN')
            ->set('needed_by', now()->addDays(5)->format('Y-m-d'))
            ->set('requisition_type', 'material')
            ->set('priority', 'normal')
            ->set('items', [$this->validItem()])
            ->call('save');

        $this->assertDatabaseHas('purchase_requisitions', [
            'justification'    => 'Se requiere para mantenimiento preventivo',
            'status'           => 'submitted',
            'requisition_type' => 'material',
            'priority'         => 'normal',
            'company_id'       => $this->user->company_id,
        ]);

        $requisition = PurchaseRequisition::first();
        $this->assertNotNull($requisition);
        $this->assertCount(1, $requisition->items);
    }

    /** @test */
    public function folio_is_generated_with_req_prefix(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación válida')
            ->set('currency', 'MXN')
            ->set('needed_by', now()->addDays(3)->format('Y-m-d'))
            ->set('requisition_type', 'material')
            ->set('priority', 'normal')
            ->set('items', [$this->validItem()])
            ->call('save');

        $this->assertStringStartsWith('REQ-', PurchaseRequisition::first()->folio);
    }

    /** @test */
    public function justification_is_required(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', '')
            ->set('items', [$this->validItem()])
            ->call('save')
            ->assertHasErrors(['justification' => 'required']);
    }

    /** @test */
    public function needed_by_is_required_and_must_be_date(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación')
            ->set('needed_by', '')
            ->set('items', [$this->validItem()])
            ->call('save')
            ->assertHasErrors(['needed_by' => 'required']);

        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación')
            ->set('needed_by', 'no-es-fecha')
            ->set('items', [$this->validItem()])
            ->call('save')
            ->assertHasErrors(['needed_by' => 'date']);
    }

    /** @test */
    public function items_require_description_and_positive_quantity(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación')
            ->set('needed_by', now()->addDays(3)->format('Y-m-d'))
            ->set('items', [$this->validItem(['description' => '', 'quantity' => 0])])
            ->call('save')
            ->assertHasErrors([
                'items.0.description' => 'required',
                'items.0.quantity'    => 'min',
            ]);
    }

    /** @test */
    public function priority_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación')
            ->set('needed_by', now()->addDays(3)->format('Y-m-d'))
            ->set('priority', 'critical')
            ->set('items', [$this->validItem()])
            ->call('save')
            ->assertHasErrors(['priority']);
    }

    /** @test */
    public function requisition_type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación')
            ->set('needed_by', now()->addDays(3)->format('Y-m-d'))
            ->set('requisition_type', 'comida')
            ->set('items', [$this->validItem()])
            ->call('save')
            ->assertHasErrors(['requisition_type']);
    }

    /** @test */
    public function can_add_and_remove_items(): void
    {
        $component = Livewire::test('App\Livewire\Purchases\RequisitionForm');

        $initialCount = count($component->get('items'));

        $component->call('addItem');
        $this->assertCount($initialCount + 1, $component->get('items'));

        $component->call('removeItem', 0);
        $this->assertCount($initialCount, $component->get('items'));
    }

    /** @test */
    public function can_create_requisition_with_product(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Stock bajo')
            ->set('currency', 'MXN')
            ->set('needed_by', now()->addDays(7)->format('Y-m-d'))
            ->set('requisition_type', 'material')
            ->set('priority', 'high')
            ->set('items', [$this->validItem([
                'product_id'  => $this->product->id,
                'description' => $this->product->name,
                'unit_price'  => $this->product->purchase_price,
                'quantity'    => 50,
            ])])
            ->call('save');

        $requisition = PurchaseRequisition::first();
        $this->assertNotNull($requisition);
        $this->assertEquals($this->product->id, $requisition->items->first()->product_id);
        $this->assertEquals(50, $requisition->items->first()->quantity);
    }

    /** @test */
    public function currency_must_be_mxn_or_usd(): void
    {
        Livewire::test('App\Livewire\Purchases\RequisitionForm')
            ->set('justification', 'Justificación')
            ->set('needed_by', now()->addDays(3)->format('Y-m-d'))
            ->set('currency', 'EUR')
            ->set('items', [$this->validItem()])
            ->call('save')
            ->assertHasErrors(['currency']);
    }
}
