<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\MeetingAssignedNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VolunteerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_user_can_create_a_draft_report_with_no_notifications_sent(): void
    {
        Notification::fake();

        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $participant = User::where('email', 'volunteer2@example.com')->first();

        $response = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'draft',
            'meetings' => [
                [
                    'name' => 'Draft Contact',
                    'phone' => '03001112222',
                    'category' => 'general',
                    'participant_ids' => [$participant->id],
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'draft');

        Notification::assertNothingSent();
    }

    public function test_user_can_submit_a_final_report(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $response = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'submitted',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'submitted');
        $this->assertDatabaseHas('daily_reports', ['user_id' => $volunteer->id, 'status' => 'submitted']);
    }

    public function test_meeting_participants_receive_notifications_on_submit(): void
    {
        Notification::fake();

        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $participant = User::where('email', 'volunteer2@example.com')->first();

        $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'submitted',
            'meetings' => [
                [
                    'name' => 'Notified Contact',
                    'phone' => '03003334444',
                    'category' => 'general',
                    'participant_ids' => [$participant->id],
                ],
            ],
        ])->assertCreated();

        Notification::assertSentTo($participant, MeetingAssignedNotification::class);
    }

    public function test_select_all_volunteers_attaches_every_active_user(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $activeVolunteerCount = User::role('user')->where('is_active', true)->count();

        $response = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'submitted',
            'meetings' => [
                [
                    'name' => 'All Volunteers Contact',
                    'phone' => '03005556666',
                    'category' => 'general',
                    'select_all_volunteers' => true,
                ],
            ],
        ]);

        $response->assertCreated();

        $meetingId = $response->json('data.meetings.0.id');

        // Every active volunteer except the creator (who doesn't notify themselves).
        $this->assertSame($activeVolunteerCount - 1, DB::table('meeting_participants')->where('meeting_id', $meetingId)->count());
    }

    public function test_non_participant_cannot_access_meeting_details(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $participant = User::where('email', 'volunteer2@example.com')->first();
        $outsider = User::where('email', 'volunteer3@example.com')->first();

        $response = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'submitted',
            'meetings' => [
                [
                    'name' => 'Private Meeting Contact',
                    'phone' => '03007778888',
                    'category' => 'general',
                    'participant_ids' => [$participant->id],
                ],
            ],
        ]);

        $meetingId = $response->json('data.meetings.0.id');

        $this->actingAs($outsider, 'sanctum')->getJson("/api/my-meetings/{$meetingId}")->assertForbidden();
        $this->actingAs($participant, 'sanctum')->getJson("/api/my-meetings/{$meetingId}")->assertOk();
    }

    public function test_participant_can_mark_meeting_as_read(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $participant = User::where('email', 'volunteer2@example.com')->first();

        $response = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'submitted',
            'meetings' => [
                [
                    'name' => 'Read Status Contact',
                    'phone' => '03009990000',
                    'category' => 'general',
                    'participant_ids' => [$participant->id],
                ],
            ],
        ]);

        $meetingId = $response->json('data.meetings.0.id');

        $this->actingAs($participant, 'sanctum')
            ->postJson("/api/my-meetings/{$meetingId}/read")
            ->assertOk();

        $this->assertDatabaseHas('meeting_participants', [
            'meeting_id' => $meetingId,
            'user_id' => $participant->id,
        ]);

        $pivot = DB::table('meeting_participants')
            ->where('meeting_id', $meetingId)
            ->where('user_id', $participant->id)
            ->first();

        $this->assertNotNull($pivot->read_at);
    }
}
