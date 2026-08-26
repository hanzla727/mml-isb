<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\User;
use App\Notifications\LeaveDecisionPendingNotification;
use App\Notifications\MeetingReminderNotification;
use App\Notifications\MissedReportReminderNotification;
use App\Notifications\TaskDeadlineNearNotification;
use App\Notifications\TaskOverdueNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_reminds_participants_of_meetings_starting_within_24_hours(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $meeting = ScheduledMeeting::create([
            'title' => 'Tomorrow Sync', 'meeting_date' => now()->addHours(20)->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00', 'organizer_id' => $admin->id,
            'status' => 'upcoming', 'created_by' => $admin->id,
        ]);
        $meeting->participants()->attach($volunteer->id);

        $this->artisan('reminders:send')->assertSuccessful();

        Notification::assertSentTo($volunteer, MeetingReminderNotification::class);
    }

    public function test_reminds_assignees_of_tasks_due_soon_and_overdue(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $dueSoon = Task::create([
            'title' => 'Due Soon Task', 'priority' => 'medium', 'status' => 'assigned',
            'due_date' => now()->addDay()->toDateString(), 'created_by' => $admin->id,
        ]);
        $dueSoon->assignees()->attach($volunteer->id);

        $overdue = Task::create([
            'title' => 'Overdue Task', 'priority' => 'medium', 'status' => 'assigned',
            'due_date' => now()->subDay()->toDateString(), 'created_by' => $admin->id,
        ]);
        $overdue->assignees()->attach($volunteer->id);

        $this->artisan('reminders:send')->assertSuccessful();

        Notification::assertSentTo($volunteer, TaskDeadlineNearNotification::class);
        Notification::assertSentTo($volunteer, TaskOverdueNotification::class);
    }

    public function test_reminds_volunteers_who_have_not_submitted_todays_report_after_cutoff(): void
    {
        Notification::fake();
        config(['reminders.report_cutoff_hour' => 0]);

        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->artisan('reminders:send')->assertSuccessful();

        Notification::assertSentTo($volunteer, MissedReportReminderNotification::class);
    }

    public function test_does_not_remind_before_cutoff_hour(): void
    {
        Notification::fake();
        config(['reminders.report_cutoff_hour' => 23]);

        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        if (now()->hour < 23) {
            $this->artisan('reminders:send')->assertSuccessful();
            Notification::assertNotSentTo($volunteer, MissedReportReminderNotification::class);
        } else {
            $this->markTestSkipped('Current hour is at/after 23, cannot test the before-cutoff branch.');
        }
    }

    public function test_reminds_reviewers_of_leave_requests_pending_over_24_hours(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $leaveRequest = LeaveRequest::create([
            'user_id' => $volunteer->id,
            'leave_type' => 'sick',
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
        ]);
        $leaveRequest->created_at = now()->subDays(2);
        $leaveRequest->save();

        $this->artisan('reminders:send')->assertSuccessful();

        Notification::assertSentTo($admin, LeaveDecisionPendingNotification::class);
    }
}
