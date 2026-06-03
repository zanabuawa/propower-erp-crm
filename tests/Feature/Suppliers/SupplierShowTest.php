<?php

namespace Tests\Feature\Suppliers;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\SupplierNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierShowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;

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
        $this->supplier = Supplier::create([
            'company_id' => $company->id,
            'name'       => 'Distribuidora Eléctrica del Norte S.A.',
            'type'       => 'company',
            'status'     => 'active',
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function save_note_creates_supplier_note(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierShow', ['supplier' => $this->supplier])
            ->set('noteTitle', 'Negociación de precios')
            ->set('noteType', 'meeting')
            ->set('noteBody', 'Se acordó descuento del 5% en pedidos mayores a $50k.')
            ->call('saveNote');

        $this->assertDatabaseHas('supplier_notes', [
            'supplier_id' => $this->supplier->id,
            'title'       => 'Negociación de precios',
            'type'        => 'meeting',
        ]);
    }

    /** @test */
    public function save_note_requires_title(): void
    {
        Livewire::test('App\Livewire\Suppliers\SupplierShow', ['supplier' => $this->supplier])
            ->set('noteTitle', '')
            ->set('noteType', 'note')
            ->call('saveNote')
            ->assertHasErrors(['noteTitle' => 'required']);
    }

    /** @test */
    public function delete_note_removes_it(): void
    {
        $note = SupplierNote::create([
            'supplier_id' => $this->supplier->id,
            'user_id'     => $this->user->id,
            'type'        => 'email',
            'title'       => 'Confirmación de pedido',
            'noted_at'    => now(),
        ]);

        Livewire::test('App\Livewire\Suppliers\SupplierShow', ['supplier' => $this->supplier])
            ->call('deleteNote', $note->id);

        $this->assertDatabaseMissing('supplier_notes', ['id' => $note->id]);
    }
}
