<?php

namespace Tests\Feature\Tenders;

use App\Models\Company;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenderTest extends TestCase
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
    public function can_create_tender(): void
    {
        Livewire::test('App\Livewire\Tenders\TenderForm')
            ->set('name', 'Licitación Obra Eléctrica Monterrey')
            ->set('type', 'obra_privada')
            ->set('status', 'borrador')
            ->set('items', [[
                'id'          => null,
                'product_id'  => null,
                'code'        => '',
                'category'    => 'Eléctrico',
                'description' => 'Instalación eléctrica industrial',
                'unit'        => 'GL',
                'quantity'    => 1,
                'unit_price'  => 500000,
                'total'       => 500000,
            ]])
            ->call('save');

        $this->assertDatabaseHas('tenders', [
            'name'   => 'Licitación Obra Eléctrica Monterrey',
            'type'   => 'obra_privada',
            'status' => 'borrador',
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Tenders\TenderForm')
            ->set('name', '')
            ->set('type', 'obra_privada')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function type_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Tenders\TenderForm')
            ->set('name', 'Licitación Prueba')
            ->set('type', 'concurso')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        Livewire::test('App\Livewire\Tenders\TenderForm')
            ->set('name', 'Licitación Prueba')
            ->set('type', 'suministro')
            ->set('status', 'pendiente')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    /** @test */
    public function can_update_existing_tender(): void
    {
        $tender = Tender::create([
            'company_id' => $this->user->company_id,
            'name'       => 'Licitación Original',
            'type'       => 'suministro',
            'status'     => 'borrador',
        ]);

        Livewire::test('App\Livewire\Tenders\TenderForm', ['tender' => $tender])
            ->set('status', 'publicada')
            ->set('estimated_budget', '1000000')
            ->set('items', [[
                'id'          => null,
                'product_id'  => null,
                'code'        => '',
                'category'    => '',
                'description' => 'Suministro eléctrico general',
                'unit'        => 'GL',
                'quantity'    => 1,
                'unit_price'  => 1000000,
                'total'       => 1000000,
            ]])
            ->call('save');

        $this->assertEquals('publicada', $tender->fresh()->status);
    }
}
