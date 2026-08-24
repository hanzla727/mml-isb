<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_cannot_see_reports_from_another_na(): void
    {
        // admin1 -> NA-48, admin2 -> NA-49 (per DemoUserSeeder).
        $admin1 = User::where('email', 'admin1@example.com')->first();
        $admin2 = User::where('email', 'admin2@example.com')->first();

        // volunteer5 lands on the Khidmat Team / NA-49 per the round-robin seed.
        $na49Volunteer = User::where('email', 'volunteer5@example.com')->first();
        $this->assertNotSame($admin1->adminNas->pluck('id')->first(), $na49Volunteer->na_id);
        $this->assertSame($admin2->adminNas->pluck('id')->first(), $na49Volunteer->na_id);

        $report = $this->actingAs($na49Volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-20',
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'status' => 'submitted',
        ]);
        $report->assertCreated();
        $reportId = $report->json('data.id');

        // admin2 (same NA) can see it; admin1 (different NA) cannot.
        $this->actingAs($admin2, 'sanctum')->getJson("/api/reports/{$reportId}")->assertOk();
        $this->actingAs($admin1, 'sanctum')->getJson("/api/reports/{$reportId}")->assertForbidden();

        // admin1's report list must not include it at all.
        $admin1List = $this->actingAs($admin1, 'sanctum')->getJson('/api/reports')->json('data');
        $this->assertFalse(collect($admin1List)->contains('id', $reportId));

        $admin2List = $this->actingAs($admin2, 'sanctum')->getJson('/api/reports')->json('data');
        $this->assertTrue(collect($admin2List)->contains('id', $reportId));
    }

    public function test_na_head_only_sees_their_own_na(): void
    {
        $naHead = User::where('email', 'nahead1@example.com')->first(); // heads NA-48.
        $ownNaVolunteer = User::where('email', 'volunteer2@example.com')->first(); // Events Team, NA-48.
        $otherNaVolunteer = User::where('email', 'volunteer5@example.com')->first(); // Khidmat Team, NA-49.

        $this->assertSame($naHead->na_id, $ownNaVolunteer->na_id);
        $this->assertNotSame($naHead->na_id, $otherNaVolunteer->na_id);

        $ownReport = $this->actingAs($ownNaVolunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-21', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $otherReport = $this->actingAs($otherNaVolunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-21', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($naHead, 'sanctum')->getJson("/api/reports/{$ownReport}")->assertOk();
        $this->actingAs($naHead, 'sanctum')->getJson("/api/reports/{$otherReport}")->assertForbidden();
    }

    public function test_team_leader_only_sees_their_own_team(): void
    {
        $teamLeader = User::where('email', 'teamleader1@example.com')->first();
        // Team Leader One leads "Donor Relations Team" — volunteer1 and volunteer8 are on it (round-robin seed).
        $ownTeamVolunteer = User::where('email', 'volunteer1@example.com')->first();
        $otherTeamVolunteer = User::where('email', 'volunteer3@example.com')->first();

        $this->assertSame($teamLeader->team_id, $ownTeamVolunteer->team_id);
        $this->assertNotSame($teamLeader->team_id, $otherTeamVolunteer->team_id);

        $ownReport = $this->actingAs($ownTeamVolunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-21', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $otherReport = $this->actingAs($otherTeamVolunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-21', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($teamLeader, 'sanctum')->getJson("/api/reports/{$ownReport}")->assertOk();
        $this->actingAs($teamLeader, 'sanctum')->getJson("/api/reports/{$otherReport}")->assertForbidden();
    }

    public function test_super_admin_sees_all_nas(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        $volunteer = User::where('email', 'volunteer5@example.com')->first();

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-22', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($superAdmin, 'sanctum')->getJson("/api/reports/{$reportId}")->assertOk();
    }
}
