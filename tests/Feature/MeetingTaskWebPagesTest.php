<?php

namespace Tests\Feature;

use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingTaskWebPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_meeting_and_task_pages_render(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $meeting = $this->actingAs($admin)->post('/admin/meetings', [
            'title' => 'Web Test Meeting',
            'meeting_date' => '2026-08-15',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ]);

        $meeting->assertRedirect();
        $meetingId = ScheduledMeeting::where('title', 'Web Test Meeting')->first()->id;

        $this->actingAs($admin)->get('/admin/meetings')->assertOk()->assertSee('Web Test Meeting');
        $this->actingAs($admin)->get("/admin/meetings/{$meetingId}")->assertOk()->assertSee('Web Test Meeting');

        $task = $this->actingAs($admin)->post('/admin/tasks', [
            'scheduled_meeting_id' => $meetingId,
            'title' => 'Web Test Task',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ]);
        $task->assertRedirect();
        $taskId = Task::where('title', 'Web Test Task')->first()->id;

        $this->actingAs($admin)->get('/admin/tasks')->assertOk()->assertSee('Web Test Task');
        $this->actingAs($admin)->get("/admin/tasks/{$taskId}")->assertOk()->assertSee('Web Test Task');
        $this->actingAs($admin)->get('/admin/task-reports')->assertOk();

        $this->actingAs($volunteer)->get('/dashboard/schedule')->assertOk();
        $this->actingAs($volunteer)->get("/dashboard/schedule/{$meetingId}")->assertOk()->assertSee('Web Test Meeting');
        $this->actingAs($volunteer)->get('/dashboard/tasks')->assertOk()->assertSee('Web Test Task');
        $this->actingAs($volunteer)->get("/dashboard/tasks/{$taskId}")->assertOk();

        $submit = $this->actingAs($volunteer)->post("/dashboard/tasks/{$taskId}/report", [
            'work_summary' => 'Done via web form.',
        ]);
        $submit->assertRedirect();

        $reportId = \App\Models\TaskReport::where('task_id', $taskId)->first()->id;
        $this->actingAs($admin)->get("/admin/task-reports/{$reportId}")->assertOk();
        $this->actingAs($admin)->put("/admin/task-reports/{$reportId}/review", ['decision' => 'approve'])->assertRedirect();
    }
}
