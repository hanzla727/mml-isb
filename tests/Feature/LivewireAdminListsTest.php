<?php

namespace Tests\Feature;

use App\Livewire\Admin\MeetingsIndex;
use App\Livewire\Admin\ReportsIndex;
use App\Livewire\Admin\TasksIndex;
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
use Livewire\Livewire;
use Tests\TestCase;

class LivewireAdminListsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_reports_index_filters_by_department_and_review_status(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteerInDept = User::where('email', 'volunteer1@example.com')->first(); // Fundraising, UC F-10.

        $this->actingAs($volunteerInDept)->postJson('/api/reports', [
            'report_date' => now()->subDay()->toDateString(), 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ]);

        Livewire::actingAs($admin)->test(ReportsIndex::class)
            ->assertSee($volunteerInDept->name)
            ->set('departmentId', $volunteerInDept->department_id)
            ->assertSee($volunteerInDept->name)
            ->set('reviewStatus', 'approved')
            ->assertSee('No reports match these filters')
            ->set('reviewStatus', '')
            ->set('departmentId', '999999')
            ->assertSee('No reports match these filters');
    }

    public function test_tasks_index_filters_by_status_priority_and_project(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $department = Department::where('name', 'Fundraising')->first();
        $uc = Uc::where('name', 'UC F-10')->first();
        $project = Project::create(['department_id' => $department->id, 'uc_id' => $uc->id, 'name' => 'Filter Project', 'status' => 'active', 'created_by' => $admin->id]);

        $task = Task::create([
            'project_id' => $project->id, 'title' => 'Filterable Task', 'priority' => 'critical',
            'status' => 'assigned', 'created_by' => $admin->id,
        ]);
        $task->assignees()->attach($volunteer->id);

        Livewire::actingAs($admin)->test(TasksIndex::class)
            ->assertSee('Filterable Task')
            ->set('priority', 'critical')
            ->assertSee('Filterable Task')
            ->set('priority', 'low')
            ->assertDontSee('Filterable Task')
            ->set('priority', '')
            ->set('projectId', $project->id)
            ->assertSee('Filterable Task')
            ->set('status', 'completed')
            ->assertDontSee('Filterable Task');
    }

    public function test_meetings_index_filters_by_search_and_date_range(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();

        $volunteerInDept = User::where('email', 'volunteer1@example.com')->first();

        $meeting = ScheduledMeeting::create([
            'title' => 'Unique Searchable Meeting', 'meeting_date' => now()->addDays(5)->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00', 'organizer_id' => $admin->id,
            'status' => 'upcoming', 'created_by' => $admin->id,
        ]);
        $meeting->participants()->attach($volunteerInDept->id);

        Livewire::actingAs($admin)->test(MeetingsIndex::class)
            ->assertSee('Unique Searchable Meeting')
            ->set('search', 'Nonexistent')
            ->assertSee('No meetings match these filters')
            ->set('search', 'Searchable')
            ->assertSee('Unique Searchable Meeting')
            ->set('search', '')
            ->set('from', now()->addDays(10)->toDateString())
            ->assertSee('No meetings match these filters');
    }

    public function test_resetting_filters_clears_all_active_filters(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();

        Livewire::actingAs($admin)->test(TasksIndex::class)
            ->set('search', 'anything')
            ->set('priority', 'high')
            ->assertSet('hasActiveFilters', true)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('priority', '')
            ->assertSet('hasActiveFilters', false);
    }
}
