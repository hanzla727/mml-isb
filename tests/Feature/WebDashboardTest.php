<?php

namespace Tests\Feature;

use App\Models\Uc;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_login_redirects_super_admin_to_admin_dashboard(): void
    {
        $this->post('/login', ['email' => 'superadmin@example.com', 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_login_redirects_admin_to_admin_dashboard(): void
    {
        $this->post('/login', ['email' => 'admin1@example.com', 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_login_redirects_volunteer_to_user_dashboard(): void
    {
        $this->post('/login', ['email' => 'volunteer1@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    public function test_volunteer_cannot_access_admin_area_and_admin_cannot_access_user_area(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $this->actingAs($volunteer)->get('/admin')->assertForbidden();

        $admin = User::where('email', 'admin1@example.com')->first();
        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
    }

    public function test_admin_and_super_admin_dashboards_render(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Total Volunteers');

        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        $this->actingAs($superAdmin)->get('/admin')->assertOk()->assertSee('Total Volunteers');
    }

    public function test_admin_can_view_users_list_but_not_manage_users(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertDontSee('Add User');
        $this->actingAs($admin)->get('/admin/users/create')->assertForbidden();
        $this->actingAs($admin)->post('/admin/announcements', [
            'title' => 'x', 'body' => 'x', 'category' => 'general', 'audience_scope' => 'all',
        ])->assertForbidden();
    }

    public function test_super_admin_can_manage_users_departments_and_teams(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        $uc = Uc::first();

        $this->actingAs($superAdmin)->get('/admin/users')->assertOk()->assertSee('Add User');

        $this->actingAs($superAdmin)->post('/admin/departments', [
            'name' => 'Welfare',
            'description' => 'Welfare activities',
        ])->assertRedirect();

        $department = \App\Models\Department::where('name', 'Welfare')->first();
        $this->assertNotNull($department);

        $this->actingAs($superAdmin)->post('/admin/teams', [
            'department_id' => $department->id,
            'uc_id' => $uc->id,
            'name' => 'Welfare Response Team',
        ])->assertRedirect();

        $this->assertDatabaseHas('teams', ['name' => 'Welfare Response Team', 'department_id' => $department->id, 'uc_id' => $uc->id]);
    }

    public function test_volunteer_sees_own_dashboard_and_reports(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->get('/dashboard')->assertOk()->assertSee('Hours Worked Today');
        $this->actingAs($volunteer)->get('/dashboard/reports')->assertOk();
        $this->actingAs($volunteer)->get('/dashboard/targets')->assertOk();
        $this->actingAs($volunteer)->get('/dashboard/announcements')->assertOk();
        $this->actingAs($volunteer)->get('/dashboard/progress')->assertOk();
    }

    public function test_admin_can_export_reports_as_csv(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();

        $response = $this->actingAs($admin)->get('/admin/reports/export?format=csv');

        $response->assertOk();
        $this->assertStringContainsString('Volunteer', $response->streamedContent());
    }

    public function test_the_how_it_works_guide_is_reachable_by_every_role(): void
    {
        foreach (['superadmin@example.com', 'admin1@example.com', 'nahead1@example.com', 'teamleader1@example.com', 'volunteer1@example.com'] as $email) {
            $user = User::where('email', $email)->first();

            $this->actingAs($user)->get(route('guide'))
                ->assertOk()
                ->assertSee('How This System Works');
        }
    }
}
