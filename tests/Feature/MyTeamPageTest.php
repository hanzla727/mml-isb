<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyTeamPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_team_leader_sees_only_their_own_team_members_on_my_team_page(): void
    {
        $teamLeader = User::where('email', 'teamleader1@example.com')->first();
        $teamMember = User::where('email', 'volunteer1@example.com')->first();
        $outsider = User::where('email', 'volunteer3@example.com')->first();

        $response = $this->actingAs($teamLeader)->get('/admin/my-team');

        $response->assertOk()->assertSee($teamMember->name)->assertDontSee($outsider->name);
    }

    public function test_volunteer_without_manage_team_permission_cannot_access_my_team_page(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->get('/admin/my-team')->assertForbidden();
    }
}
