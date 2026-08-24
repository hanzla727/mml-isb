<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Project;
use App\Models\Uc;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_dashboard_shows_volunteers_on_leave_and_active_projects(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $department = Department::where('name', 'Fundraising')->first();
        $uc = Uc::where('name', 'UC F-10')->first();

        LeaveRequest::create([
            'user_id' => $volunteer->id, 'leave_type' => 'sick',
            'start_date' => now()->subDay()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now(),
        ]);

        Project::create(['department_id' => $department->id, 'uc_id' => $uc->id, 'name' => 'Winter Drive', 'status' => 'active', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk()->assertSee('Winter Drive');
        $response->assertSee('1', false);
    }

    public function test_volunteer_dashboard_shows_leave_status_and_projects(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        LeaveRequest::create([
            'user_id' => $volunteer->id, 'leave_type' => 'personal',
            'start_date' => now()->addDays(5)->toDateString(), 'end_date' => now()->addDays(6)->toDateString(),
        ]);

        $this->actingAs($volunteer)->get('/dashboard')->assertOk()->assertSee('Leave Status');
    }
}
