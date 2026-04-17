<?php

namespace Tests\Feature\HR;

use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\HrPosition;
use App\Models\HrProspect;
use App\Models\User;
use App\Notifications\HR\InterviewReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ProspectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $company = Company::create([
            'name' => 'Test Company',
            'legal_name' => 'Test Company S.A.',
            'rfc' => 'ABC123456789',
        ]);
        
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        
        if (method_exists($user, 'givePermissionTo')) {
            $user->givePermissionTo('create hr', 'view hr', 'edit hr');
        }
        
        $this->actingAs($user);
    }

    /** @test */
    public function can_create_prospect()
    {
        $department = HrDepartment::create([
            'company_id' => auth()->user()->company_id,
            'name' => 'IT',
            'is_active' => true,
        ]);

        $position = HrPosition::create([
            'company_id' => auth()->user()->company_id,
            'department_id' => $department->id,
            'name' => 'Developer',
            'is_active' => true,
        ]);

        Livewire::test('App\Livewire\HR\ProspectForm')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@example.com')
            ->set('phone', '1234567890')
            ->set('position_id', $position->id)
            ->set('source', 'linkedin')
            ->set('initial_notes', 'Test notes')
            ->call('save');

        $this->assertTrue(HrProspect::where('first_name', 'John')->exists());
        $this->assertEquals('nuevo', HrProspect::first()->status);
        $this->assertDatabaseHas('hr_prospect_status_logs', [
            'to_status' => 'nuevo',
        ]);
    }

    /** @test */
    public function rejection_requires_reason()
    {
        $prospect = HrProspect::create([
            'company_id' => auth()->user()->company_id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'status' => 'nuevo',
        ]);

        Livewire::test('App\Livewire\HR\ProspectForm', ['prospect' => $prospect])
            ->set('status', 'rechazado')
            ->set('status_reason', '')
            ->call('save')
            ->assertHasErrors(['status_reason' => 'required']);

        Livewire::test('App\Livewire\HR\ProspectForm', ['prospect' => $prospect])
            ->set('status', 'rechazado')
            ->set('status_reason', 'Not a good fit')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('rechazado', $prospect->fresh()->status);
        $this->assertDatabaseHas('hr_prospect_status_logs', [
            'prospect_id' => $prospect->id,
            'to_status' => 'rechazado',
            'reason' => 'Not a good fit',
        ]);
    }

    /** @test */
    public function can_schedule_interview()
    {
        $prospect = HrProspect::create([
            'company_id' => auth()->user()->company_id,
            'first_name' => 'Bob',
            'last_name' => 'Sponge',
            'status' => 'nuevo',
        ]);

        Livewire::test('App\Livewire\HR\ProspectAgenda')
            ->set('selectedProspectId', $prospect->id)
            ->set('interview_date', '2026-05-01')
            ->set('interview_time', '10:00')
            ->set('interview_type', 'virtual')
            ->set('interviewer_id', auth()->id())
            ->call('scheduleInterview');

        $prospect->refresh();
        $this->assertEquals('entrevista_agendada', $prospect->status);
        $this->assertEquals('virtual', $prospect->interview_type);
        $this->assertEquals('2026-05-01 10:00:00', $prospect->interview_date->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('hr_prospect_status_logs', [
            'prospect_id' => $prospect->id,
            'to_status' => 'entrevista_agendada',
        ]);
    }

    /** @test */
    public function sends_interview_reminders()
    {
        Notification::fake();

        $interviewer = User::create([
            'name' => 'Interviewer',
            'email' => 'interviewer@example.com',
            'password' => bcrypt('password'),
            'company_id' => auth()->user()->company_id,
        ]);

        $prospect = HrProspect::create([
            'company_id' => auth()->user()->company_id,
            'first_name' => 'Reminder',
            'last_name' => 'Test',
            'status' => 'entrevista_agendada',
            'interview_date' => now()->addMinutes(30),
            'interviewer_id' => $interviewer->id,
            'scheduled_by_id' => auth()->id(),
        ]);

        $this->artisan('hr:send-interview-reminders');

        Notification::assertSentTo(
            [$interviewer, auth()->user()],
            InterviewReminderNotification::class,
            fn ($notification) => $notification->timeframe === '1h'
        );

        $this->assertTrue($prospect->fresh()->reminder_1h_sent);
    }
}
