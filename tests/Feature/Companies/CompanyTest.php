<?php

namespace Tests\Feature\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::create([
            'name'       => 'Test Co',
            'legal_name' => 'Test Co S.A. de C.V.',
            'rfc'        => 'ABC123456789',
        ]);
        $this->user = User::create([
            'name'       => 'Admin',
            'email'      => 'admin@test.com',
            'password'   => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function can_update_company(): void
    {
        Livewire::test('App\Livewire\Companies\CompanyForm', ['company' => $this->company])
            ->set('name', 'Test Co Actualizado')
            ->set('email', 'contacto@testco.com')
            ->set('phone', '8181234567')
            ->call('save');

        $this->assertEquals('Test Co Actualizado', $this->company->fresh()->name);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Companies\CompanyForm', ['company' => $this->company])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function email_must_be_valid_format(): void
    {
        Livewire::test('App\Livewire\Companies\CompanyForm', ['company' => $this->company])
            ->set('name', 'Test Co')
            ->set('email', 'no-es-email')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    /** @test */
    public function rfc_max_length_is_13(): void
    {
        Livewire::test('App\Livewire\Companies\CompanyForm', ['company' => $this->company])
            ->set('name', 'Test Co')
            ->set('rfc', 'ABCDEFGHIJKLMNO')
            ->call('save')
            ->assertHasErrors(['rfc']);
    }

    /** @test */
    public function country_is_required(): void
    {
        Livewire::test('App\Livewire\Companies\CompanyForm', ['company' => $this->company])
            ->set('name', 'Test Co')
            ->set('country', '')
            ->call('save')
            ->assertHasErrors(['country' => 'required']);
    }
}
