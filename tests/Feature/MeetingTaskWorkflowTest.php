<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\Uc;
use App\Models\User;
use App\Notifications\MeetingCreatedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskNeedsRevisionNotification;
use App\Notifications\TaskReportSubmittedNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class MeetingTaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
        Storage::fake('public');
    }

    public function test_admin_creates_meeting_for_entire_uc_and_all_members_are_notified(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $uc = Uc::where('name', 'UC F-10')->firstOrFail();
        $ucMembers = User::where('uc_id', $uc->id)->where('is_active', true)->get();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/meetings', [
            'title' => 'Weekly Sync',
            'meeting_date' => '2026-08-10',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'scope' => 'uc',
            'uc_id' => $uc->id,
        ]);

        $response->assertCreated();

        foreach ($ucMembers as $member) {
            $this->assertDatabaseHas('scheduled_meeting_participants', [
                'scheduled_meeting_id' => $response->json('data.id'),
                'user_id' => $member->id,
            ]);
            Notification::assertSentTo($member, MeetingCreatedNotification::class);
        }
    }

    public function test_super_admin_assigns_task_to_all_users_and_each_is_notified(): void
    {
        Notification::fake();

        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        // scope=all means every active user org-wide (not just volunteers) —
        // admins/super_admin can be assigned tasks too. Only Super Admin is
        // truly unrestricted, so this uses Super Admin rather than a
        // scoped Admin (for whom 'all' means everyone *they* can see).
        $activeUserCount = User::where('is_active', true)->count();

        $response = $this->actingAs($superAdmin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Prepare quarterly report',
            'priority' => 'high',
            'scope' => 'all',
        ]);

        $response->assertCreated();
        $taskId = $response->json('data.id');

        $this->assertSame($activeUserCount, DB::table('task_assignees')->where('task_id', $taskId)->count());

        $activeUsers = User::where('is_active', true)->get();
        Notification::assertSentTo($activeUsers, TaskAssignedNotification::class);
    }

    public function test_scoped_admin_assigning_task_to_all_only_reaches_their_own_scope(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first(); // NA-48 + NA-50 only.
        $visibleCount = count(\App\Services\HierarchyScope::visibleUserIds($admin));

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Prepare regional report',
            'priority' => 'high',
            'scope' => 'all',
        ]);

        $response->assertCreated();
        $taskId = $response->json('data.id');

        $assignedCount = DB::table('task_assignees')->where('task_id', $taskId)->count();
        $this->assertSame($visibleCount, $assignedCount);
        $this->assertLessThan(User::where('is_active', true)->count(), $assignedCount);
    }

    public function test_assignee_submits_report_and_reviewer_is_notified_then_admin_can_approve(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $task = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Submit donor list',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->json('data');

        $submit = $this->actingAs($volunteer, 'sanctum')->postJson("/api/tasks/{$task['id']}/reports", [
            'work_summary' => 'Compiled the donor list.',
            'working_hours' => 3,
        ]);

        $submit->assertCreated();
        $this->assertDatabaseHas('tasks', ['id' => $task['id'], 'status' => 'report_submitted']);
        Notification::assertSentTo($admin, TaskReportSubmittedNotification::class);

        $reportId = $submit->json('data.id');

        $approve = $this->actingAs($admin, 'sanctum')->putJson("/api/admin/task-reports/{$reportId}/review", [
            'decision' => 'approve',
        ]);

        $approve->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $task['id'], 'status' => 'approved']);
        $this->assertDatabaseHas('task_reports', ['id' => $reportId, 'review_status' => 'approved']);
    }

    public function test_rejection_notifies_assignee_and_resubmission_increments_version(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer2@example.com')->first();

        $task = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Draft event plan',
            'priority' => 'low',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->json('data');

        $firstReport = $this->actingAs($volunteer, 'sanctum')->postJson("/api/tasks/{$task['id']}/reports", [
            'work_summary' => 'First draft.',
        ])->json('data');

        $this->actingAs($admin, 'sanctum')->putJson("/api/admin/task-reports/{$firstReport['id']}/review", [
            'decision' => 'return_for_revision',
            'remarks' => 'Please add a budget breakdown.',
        ])->assertOk();

        Notification::assertSentTo($volunteer, TaskNeedsRevisionNotification::class);
        $this->assertDatabaseHas('tasks', ['id' => $task['id'], 'status' => 'needs_revision']);

        $secondReport = $this->actingAs($volunteer, 'sanctum')->postJson("/api/tasks/{$task['id']}/reports", [
            'work_summary' => 'Second draft with budget.',
        ])->json('data');

        $this->assertSame(2, $secondReport['version']);
        $this->assertSame('re_submitted', $secondReport['review_status']);
    }

    public function test_non_assignee_cannot_view_task(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $outsider = User::where('email', 'volunteer3@example.com')->first();

        $task = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Confidential task',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->json('data');

        $this->actingAs($outsider, 'sanctum')->getJson("/api/tasks/{$task['id']}")->assertForbidden();
        $this->actingAs($volunteer, 'sanctum')->getJson("/api/tasks/{$task['id']}")->assertOk();
    }

    public function test_audit_log_records_create_assign_submit_and_review_actions(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $task = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Auditable task',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->json('data');

        $report = $this->actingAs($volunteer, 'sanctum')->postJson("/api/tasks/{$task['id']}/reports", [
            'work_summary' => 'Done.',
        ])->json('data');

        $this->actingAs($admin, 'sanctum')->putJson("/api/admin/task-reports/{$report['id']}/review", [
            'decision' => 'approve',
        ]);

        $this->assertGreaterThan(0, Activity::where('subject_type', \App\Models\Task::class)->where('subject_id', $task['id'])->count());
        $this->assertGreaterThan(0, Activity::where('subject_type', \App\Models\TaskReport::class)->where('subject_id', $report['id'])->count());
    }

    public function test_adding_a_comment_notifies_the_other_assignees_and_reviewers(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $otherVolunteer = User::where('email', 'volunteer2@example.com')->first();

        $task = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/tasks', [
            'title' => 'Task with two assignees',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id, $otherVolunteer->id],
        ])->json('data');

        $response = $this->actingAs($volunteer, 'sanctum')->postJson("/api/tasks/{$task['id']}/comments", [
            'body' => 'Any update on this one?',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('task_comments', ['task_id' => $task['id'], 'user_id' => $volunteer->id, 'body' => 'Any update on this one?']);

        Notification::assertSentTo($otherVolunteer, \App\Notifications\NewTaskCommentNotification::class);
        Notification::assertNotSentTo($volunteer, \App\Notifications\NewTaskCommentNotification::class);
    }

    public function test_admin_creates_standalone_task_without_a_meeting_and_can_assign_it_to_any_role(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $ucHead = User::where('email', 'uchead1@example.com')->first();
        $naHead = User::where('email', 'nahead1@example.com')->first();

        $response = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'title' => 'Standalone task with no meeting',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$ucHead->id, $naHead->id],
        ]);

        $response->assertRedirect();

        $task = \App\Models\Task::where('title', 'Standalone task with no meeting')->firstOrFail();

        $this->assertNull($task->scheduled_meeting_id);
        $this->assertDatabaseHas('task_assignees', ['task_id' => $task->id, 'user_id' => $ucHead->id]);
        $this->assertDatabaseHas('task_assignees', ['task_id' => $task->id, 'user_id' => $naHead->id]);
        Notification::assertSentTo([$ucHead, $naHead], TaskAssignedNotification::class);
    }

    public function test_assignee_sees_task_on_login_submits_report_with_receipt_and_admin_approves_via_web(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $task = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'title' => 'Collect donations at the mosque',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ]);
        $task->assertRedirect();

        $createdTask = Task::where('title', 'Collect donations at the mosque')->firstOrFail();

        // Volunteer logs in and sees the assigned task on their task list.
        $this->actingAs($volunteer)->get(route('user.tasks.index'))
            ->assertOk()
            ->assertSee('Collect donations at the mosque');

        // Volunteer reports the work done and uploads the receipt.
        $submit = $this->actingAs($volunteer)->post(route('user.tasks.submit-report', $createdTask), [
            'work_summary' => 'Collected donations after Friday prayer.',
            'working_hours' => 2,
            'amount_collected' => 5000,
            'attachments' => [UploadedFile::fake()->create('receipt.pdf', 100)],
        ]);
        $submit->assertRedirect(route('user.tasks.show', $createdTask));

        $report = $createdTask->reports()->where('user_id', $volunteer->id)->firstOrFail();
        $this->assertSame('5000.00', $report->amount_collected);
        $this->assertCount(1, $report->attachments);
        $this->assertDatabaseHas('tasks', ['id' => $createdTask->id, 'status' => 'report_submitted']);

        // Admin approves the report.
        $approve = $this->actingAs($admin)->put(route('admin.task-reports.review', $report), [
            'decision' => 'approve',
        ]);
        $approve->assertRedirect(route('admin.task-reports.index'));

        $this->assertDatabaseHas('task_reports', ['id' => $report->id, 'review_status' => 'approved']);
        $this->assertDatabaseHas('tasks', ['id' => $createdTask->id, 'status' => 'approved']);
    }
}
