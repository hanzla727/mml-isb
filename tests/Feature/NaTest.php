<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Na;
use App\Models\Project;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\Uc;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_super_admin_can_create_a_na_and_assign_a_na_head(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        $candidate = User::where('email', 'volunteer5@example.com')->first();

        $this->actingAs($superAdmin)->post('/admin/nas', [
            'name' => 'NA-50',
            'status' => 'active',
        ])->assertRedirect();

        $na = Na::where('name', 'NA-50')->first();
        $this->assertNotNull($na);

        $this->actingAs($superAdmin)->put("/admin/nas/{$na->id}", [
            'name' => 'NA-50',
            'status' => 'active',
            'na_head_id' => $candidate->id,
        ])->assertRedirect();

        $this->assertSame($candidate->id, $na->fresh()->na_head_id);
        $this->assertTrue($candidate->fresh()->hasRole('na_head'));
        $this->assertSame($na->id, $candidate->fresh()->na_id);
    }

    public function test_admin_without_manage_nas_permission_cannot_create_a_na(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();

        $this->actingAs($admin)->post('/admin/nas', [
            'name' => 'Should Not Exist', 'status' => 'active',
        ])->assertForbidden();
    }

    public function test_admin_only_sees_nas_they_are_assigned_to(): void
    {
        $admin1 = User::where('email', 'admin1@example.com')->first(); // NA-48 only.

        $response = $this->actingAs($admin1)->get('/admin/nas');

        $response->assertOk()->assertSee('NA-48')->assertDontSee('NA-49');
    }

    public function test_na_dashboard_shows_performance_metrics_and_pending_items(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $na = Na::where('name', 'NA-48')->first();
        $ucF10 = Uc::where('name', 'UC F-10')->first();
        $department = Department::where('name', 'Fundraising')->first();

        $task = Task::create([
            'title' => 'Pending NA Task', 'priority' => 'medium', 'status' => 'assigned', 'created_by' => $admin->id,
        ]);
        $task->assignees()->attach($volunteer->id);

        ScheduledMeeting::create([
            'title' => 'Upcoming NA Meeting', 'meeting_date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00', 'organizer_id' => $admin->id,
            'status' => 'upcoming', 'created_by' => $admin->id,
        ])->participants()->attach($volunteer->id);

        Project::create(['department_id' => $department->id, 'uc_id' => $ucF10->id, 'name' => 'NA Dashboard Project', 'status' => 'active', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get("/admin/nas/{$na->id}");

        $response->assertOk()
            ->assertSee('Pending NA Task')
            ->assertSee('Upcoming NA Meeting')
            ->assertSee('Volunteers');
    }

    public function test_admin_cannot_view_dashboard_of_a_na_they_are_not_assigned_to(): void
    {
        $admin1 = User::where('email', 'admin1@example.com')->first(); // NA-48 only.
        $na49 = Na::where('name', 'NA-49')->first();

        $this->actingAs($admin1)->get("/admin/nas/{$na49->id}")->assertForbidden();
    }

    public function test_na_comparison_ranks_nas_by_score_and_is_not_a_volunteer_leaderboard(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();

        $response = $this->actingAs($superAdmin)->get('/admin/nas-compare');

        $response->assertOk()
            ->assertSee('NA-48')
            ->assertSee('NA-49')
            ->assertSee('NA Ranking')
            ->assertDontSee('Most Active Volunteers')
            ->assertDontSee('Least Active Volunteers');
    }

    public function test_volunteers_on_leave_only_counted_within_the_admins_nas(): void
    {
        $admin1 = User::where('email', 'admin1@example.com')->first(); // NA-48 + NA-50.
        $admin1NaIds = $admin1->adminNas->pluck('id');
        $ownScopeVolunteer = User::role('user')->whereIn('na_id', $admin1NaIds)->firstOrFail();
        $otherScopeVolunteer = User::role('user')->whereNotIn('na_id', $admin1NaIds)->firstOrFail();

        foreach ([$ownScopeVolunteer, $otherScopeVolunteer] as $volunteer) {
            LeaveRequest::create([
                'user_id' => $volunteer->id, 'leave_type' => 'sick',
                'start_date' => now()->subDay()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
                'status' => 'approved', 'reviewed_by' => $admin1->id, 'reviewed_at' => now(),
            ]);
        }

        // Only the in-scope volunteer's leave should count toward admin1's scope.
        $stats = app(\App\Services\DashboardMetrics::class)->forAdmin($admin1);
        $this->assertSame(1, $stats['volunteers_on_leave']);
    }
}
