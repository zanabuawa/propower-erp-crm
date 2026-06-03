<?php

namespace Tests\Feature\Users;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->company = Company::create([
            'name' => 'Test Co', 'legal_name' => 'Test Co S.A.', 'rfc' => 'ABC123456789',
        ]);
        $this->adminUser = User::create([
            'name'       => 'Admin',
            'email'      => 'admin@test.com',
            'password'   => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);
        $this->actingAs($this->adminUser);
    }

    /** @test */
    public function can_create_user(): void
    {
        Livewire::test('App\Livewire\Users\UserForm')
            ->set('name', 'Nuevo Usuario')
            ->set('email', 'nuevo@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('company_id', $this->company->id)
            ->set('role', 'admin')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@test.com',
            'name'  => 'Nuevo Usuario',
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\Users\UserForm')
            ->set('name', '')
            ->set('email', 'test@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function email_must_be_unique(): void
    {
        Livewire::test('App\Livewire\Users\UserForm')
            ->set('name', 'Duplicado')
            ->set('email', 'admin@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    /** @test */
    public function password_is_required_for_new_user(): void
    {
        Livewire::test('App\Livewire\Users\UserForm')
            ->set('name', 'Sin Password')
            ->set('email', 'sinpass@test.com')
            ->set('password', '')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors(['password' => 'required']);
    }

    /** @test */
    public function password_must_be_confirmed(): void
    {
        Livewire::test('App\Livewire\Users\UserForm')
            ->set('name', 'Usuario Prueba')
            ->set('email', 'prueba@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'diferente456')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    /** @test */
    public function role_must_exist(): void
    {
        Livewire::test('App\Livewire\Users\UserForm')
            ->set('name', 'Usuario Prueba')
            ->set('email', 'prueba@test.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'superadmin_falso')
            ->call('save')
            ->assertHasErrors(['role']);
    }

    /** @test */
    public function can_update_existing_user(): void
    {
        $user = User::create([
            'name'       => 'Usuario Existente',
            'email'      => 'existente@test.com',
            'password'   => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);
        $user->assignRole('admin');

        Livewire::test('App\Livewire\Users\UserForm', ['user' => $user])
            ->set('name', 'Nombre Actualizado')
            ->set('role', 'admin')
            ->call('save');

        $this->assertEquals('Nombre Actualizado', $user->fresh()->name);
    }
}
