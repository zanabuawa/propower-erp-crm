<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrAttendanceLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceLocationTest extends TestCase
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
    public function can_create_attendance_location(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceLocationForm')
            ->set('name', 'Oficina Corporativa')
            ->set('latitude', '25.6866')
            ->set('longitude', '-100.3161')
            ->set('radius_meters', 150)
            ->call('save');

        $this->assertDatabaseHas('hr_attendance_locations', [
            'name'          => 'Oficina Corporativa',
            'radius_meters' => 150,
        ]);
    }

    /** @test */
    public function name_is_required(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceLocationForm')
            ->set('name', '')
            ->set('latitude', '25.6866')
            ->set('longitude', '-100.3161')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function latitude_is_required(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceLocationForm')
            ->set('name', 'Planta Norte')
            ->set('latitude', '')
            ->set('longitude', '-100.3161')
            ->call('save')
            ->assertHasErrors(['latitude' => 'required']);
    }

    /** @test */
    public function latitude_must_be_within_range(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceLocationForm')
            ->set('name', 'Planta Norte')
            ->set('latitude', '95')
            ->set('longitude', '-100.3161')
            ->call('save')
            ->assertHasErrors(['latitude']);
    }

    /** @test */
    public function longitude_must_be_within_range(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceLocationForm')
            ->set('name', 'Planta Norte')
            ->set('latitude', '25.6866')
            ->set('longitude', '-200')
            ->call('save')
            ->assertHasErrors(['longitude']);
    }

    /** @test */
    public function radius_must_be_at_least_10_meters(): void
    {
        Livewire::test('App\Livewire\HR\AttendanceLocationForm')
            ->set('name', 'Planta Norte')
            ->set('latitude', '25.6866')
            ->set('longitude', '-100.3161')
            ->set('radius_meters', 5)
            ->call('save')
            ->assertHasErrors(['radius_meters']);
    }

    /** @test */
    public function can_update_existing_location(): void
    {
        $loc = HrAttendanceLocation::create([
            'company_id'   => $this->user->company_id,
            'name'         => 'Sucursal Original',
            'latitude'     => 25.68,
            'longitude'    => -100.31,
            'radius_meters'=> 100,
            'is_active'    => true,
        ]);

        Livewire::test('App\Livewire\HR\AttendanceLocationForm', ['attendanceLocation' => $loc])
            ->set('radius_meters', 200)
            ->set('notes', 'Ampliado el radio por remodelación.')
            ->call('save');

        $this->assertEquals(200, $loc->fresh()->radius_meters);
    }
}
