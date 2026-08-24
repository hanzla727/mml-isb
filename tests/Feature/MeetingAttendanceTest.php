<?php

namespace Tests\Feature;

use App\Models\ScheduledMeeting;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_marks_attendance_for_meeting_participants(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/meetings', [
            'title' => 'Attendance Meeting',
            'meeting_date' => '2026-08-15',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->assertRedirect();

        $meeting = ScheduledMeeting::where('title', 'Attendance Meeting')->first();

        $response = $this->actingAs($admin)->put("/admin/meetings/{$meeting->id}/attendance", [
            'attendance' => [$volunteer->id => 'present'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('meeting_attendances', [
            'scheduled_meeting_id' => $meeting->id,
            'user_id' => $volunteer->id,
            'status' => 'present',
            'marked_by' => $admin->id,
        ]);

        $this->actingAs($volunteer)->get("/dashboard/schedule/{$meeting->id}")->assertOk()->assertSee('Present');
    }

    public function test_non_organizer_without_manage_meetings_permission_cannot_mark_attendance(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $otherVolunteer = User::where('email', 'volunteer2@example.com')->first();
        $admin = User::where('email', 'admin1@example.com')->first();

        $this->actingAs($admin)->post('/admin/meetings', [
            'title' => 'Locked Meeting',
            'meeting_date' => '2026-08-16',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->assertRedirect();

        $meeting = ScheduledMeeting::where('title', 'Locked Meeting')->first();

        $this->actingAs($otherVolunteer)->put("/admin/meetings/{$meeting->id}/attendance", [
            'attendance' => [$volunteer->id => 'present'],
        ])->assertForbidden();
    }

    public function test_dashboard_attendance_rate_reflects_marked_attendance(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/meetings', [
            'title' => 'Rate Meeting',
            'meeting_date' => '2026-08-17',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->assertRedirect();

        $meeting = ScheduledMeeting::where('title', 'Rate Meeting')->first();

        $this->actingAs($admin)->put("/admin/meetings/{$meeting->id}/attendance", [
            'attendance' => [$volunteer->id => 'present'],
        ])->assertRedirect();

        $metrics = app(\App\Services\DashboardMetrics::class)->forAdmin($admin);

        $this->assertSame(100.0, $metrics['meetings']['attendance_rate']);
    }
}
