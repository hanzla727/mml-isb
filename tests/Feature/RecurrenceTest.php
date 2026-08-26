<?php

namespace Tests\Feature;

use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RecurrenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_creating_a_recurring_weekly_meeting_immediately_generates_upcoming_occurrences(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/meetings', [
            'title' => 'Weekly Standup',
            'meeting_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
            'is_recurring' => '1',
            'recurrence_frequency' => 'weekly',
            'recurrence_interval' => '1',
            'recurrence_until' => now()->addDays(45)->toDateString(),
        ])->assertRedirect();

        $template = ScheduledMeeting::where('title', 'Weekly Standup')->whereNull('recurring_parent_id')->first();
        $this->assertNotNull($template);
        $this->assertSame('weekly', $template->recurrence_rule['frequency']);

        $occurrences = ScheduledMeeting::where('recurring_parent_id', $template->id)->get();
        $this->assertGreaterThan(0, $occurrences->count());

        foreach ($occurrences as $occurrence) {
            $this->assertTrue($occurrence->isParticipant($volunteer));
        }
    }

    public function test_creating_a_recurring_task_generates_upcoming_occurrences_with_same_assignees(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/tasks', [
            'title' => 'Weekly Cleanup',
            'priority' => 'medium',
            'due_date' => now()->addDay()->toDateString(),
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
            'is_recurring' => '1',
            'recurrence_frequency' => 'weekly',
            'recurrence_interval' => '1',
            'recurrence_until' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();

        $template = Task::where('title', 'Weekly Cleanup')->whereNull('recurring_parent_id')->first();
        $this->assertNotNull($template);

        $occurrences = Task::where('recurring_parent_id', $template->id)->get();
        $this->assertGreaterThan(0, $occurrences->count());

        foreach ($occurrences as $occurrence) {
            $this->assertTrue($occurrence->isAssignee($volunteer));
            $this->assertSame('assigned', $occurrence->status);
        }
    }

    public function test_generate_recurring_commands_extend_existing_templates_without_duplicating(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/meetings', [
            'title' => 'Monthly Review',
            'meeting_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
            'is_recurring' => '1',
            'recurrence_frequency' => 'monthly',
            'recurrence_interval' => '1',
        ])->assertRedirect();

        $template = ScheduledMeeting::where('title', 'Monthly Review')->whereNull('recurring_parent_id')->first();
        $countBefore = ScheduledMeeting::where('recurring_parent_id', $template->id)->count();

        $this->artisan('meetings:generate-recurring')->assertSuccessful();

        $countAfter = ScheduledMeeting::where('recurring_parent_id', $template->id)->count();
        $this->assertSame($countBefore, $countAfter);
    }
}
