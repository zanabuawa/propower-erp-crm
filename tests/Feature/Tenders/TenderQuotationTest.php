<?php

namespace Tests\Feature\Tenders;

use App\Models\Company;
use App\Models\Tender;
use App\Models\TenderItem;
use App\Models\TenderQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenderQuotationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Tender $tender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::create([
            'name' => 'Test Co', 'legal_name' => 'Test Co S.A.', 'rfc' => 'ABC123456789',
        ]);
        $this->user = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'company_id' => $this->company->id,
        ]);
        $this->tender = Tender::create([
            'company_id' => $this->company->id,
            'name'       => 'Licitación Eléctrica Monterrey',
            'type'       => 'obra_privada',
            'status'     => 'publicada',
        ]);
        TenderItem::create([
            'tender_id'   => $this->tender->id,
            'description' => 'Instalación tablero eléctrico 200A',
            'unit'        => 'PZA',
            'quantity'    => 1,
            'unit_price'  => 45000,
            'total'       => 45000,
            'sort_order'  => 0,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_create_quotation(): void
    {
        Livewire::test('App\Livewire\Tenders\QuotationForm', ['tender' => $this->tender])
            ->set('issuing_company_id', $this->company->id)
            ->set('status', 'borrador')
            ->call('save');

        $this->assertDatabaseHas('tender_quotations', [
            'tender_id'          => $this->tender->id,
            'issuing_company_id' => $this->company->id,
            'status'             => 'borrador',
        ]);
    }

    /** @test */
    public function issuing_company_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\QuotationForm', ['tender' => $this->tender])
            ->set('issuing_company_id', null)
            ->call('save')
            ->assertHasErrors(['issuing_company_id' => 'required']);
    }

    /** @test */
    public function item_description_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\QuotationForm', ['tender' => $this->tender])
            ->set('issuing_company_id', $this->company->id)
            ->set('items', [[
                'tender_item_id' => null,
                'description'    => '',
                'unit'           => 'PZA',
                'quantity'       => 1,
                'unit_price'     => 1000,
                'total'          => 1000,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.description']);
    }

    /** @test */
    public function valid_until_must_be_a_date(): void
    {
        Livewire::test('App\Livewire\Tenders\QuotationForm', ['tender' => $this->tender])
            ->set('issuing_company_id', $this->company->id)
            ->set('valid_until', 'no-es-fecha')
            ->call('save')
            ->assertHasErrors(['valid_until']);
    }

    /** @test */
    public function can_update_existing_quotation(): void
    {
        $quotation = TenderQuotation::create([
            'tender_id'          => $this->tender->id,
            'issuing_company_id' => $this->company->id,
            'status'             => 'borrador',
            'created_by'         => $this->user->id,
        ]);

        Livewire::test('App\Livewire\Tenders\QuotationForm', ['tender' => $this->tender, 'quotation' => $quotation])
            ->set('issuing_company_id', $this->company->id)
            ->set('status', 'enviada')
            ->set('items', [[
                'tender_item_id' => null,
                'description'    => 'Suministro eléctrico general',
                'unit'           => 'GL',
                'quantity'       => 1,
                'unit_price'     => 50000,
                'total'          => 50000,
            ]])
            ->call('save');

        $this->assertEquals('enviada', $quotation->fresh()->status);
    }
}
