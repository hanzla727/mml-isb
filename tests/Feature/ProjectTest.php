<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Project;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\Uc;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_can_create_a_project_and_link_a_meeting_to_it(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $department = Department::where('name', 'Fundraising')->first();
        $uc = Uc::where('name', 'UC F-10')->first();

        $this->actingAs($admin)->post('/admin/projects', [
            'department_id' => $department->id,
            'uc_id' => $uc->id,
            'name' => 'Winter Relief Drive',
            'status' => 'active',
            'start_date' => '2026-08-01',
            'end_date' => '2026-09-01',
        ])->assertRedirect();

        $project = Project::where('name', 'Winter Relief Drive')->first();
        $this->assertNotNull($project);
        $this->assertSame($department->id, $project->department_id);

        $this->actingAs($admin)->post('/admin/meetings', [
            'project_id' => $project->id,
            'title' => 'Kickoff Meeting',
            'meeting_date' => '2026-08-05',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->assertRedirect();

        $meeting = ScheduledMeeting::where('title', 'Kickoff Meeting')->first();
        $this->assertSame($project->id, $meeting->project_id);

        $this->actingAs($admin)->get("/admin/projects/{$project->id}")->assertOk()->assertSee('Kickoff Meeting');
    }

    public function test_project_progress_reflects_completed_tasks(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $department = Department::where('name', 'Fundraising')->first();
        $uc = Uc::where('name', 'UC F-10')->first();

        $project = Project::create([
            'department_id' => $department->id,
            'uc_id' => $uc->id,
            'name' => 'Progress Project',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        Task::create(['project_id' => $project->id, 'title' => 'Task A', 'priority' => 'medium', 'status' => 'completed', 'created_by' => $admin->id]);
        Task::create(['project_id' => $project->id, 'title' => 'Task B', 'priority' => 'medium', 'status' => 'assigned', 'created_by' => $admin->id]);

        $this->assertSame(50, $project->progress());
    }

    public function test_volunteer_without_manage_projects_permission_cannot_access_projects_page(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->get('/admin/projects')->assertForbidden();
    }

    public function test_admins_project_uc_choices_are_scoped_but_departments_are_shared(): void
    {
        $admin1 = User::where('email', 'admin1@example.com')->first(); // NA-48 only.
        $ucG9 = Uc::where('name', 'UC G-9')->first(); // Belongs to NA-49.
        $khidmat = Department::where('name', 'Khidmat')->first(); // Only has teams in UC G-9.

        // Departments are a shared, org-wide list — every admin sees every
        // department regardless of which PPs they manage. Only the UC
        // choices themselves are scoped to the admin's own PP assignments.
        $response = $this->actingAs($admin1)->get('/admin/projects');
        $response->assertOk();
        $response->assertSee($khidmat->name);
        $response->assertDontSee($ucG9->name);
    }
}
