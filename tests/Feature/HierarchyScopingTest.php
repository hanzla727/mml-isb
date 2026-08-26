<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_cannot_see_reports_from_another_na(): void
    {
        $admin1 = User::where('email', 'admin1@example.com')->first();
        $admin2 = User::where('email', 'admin2@example.com')->first();

        $admin1NaIds = $admin1->adminNas->pluck('id');
        $otherAdminVolunteer = User::role('user')->whereNotIn('na_id', $admin1NaIds)->firstOrFail();
        $this->assertSame($admin2->adminNas->pluck('id')->first(), $otherAdminVolunteer->na_id);

        $report = $this->actingAs($otherAdminVolunteer, 'sanctum')->postJson('/api/reports', [
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
        $ownNaVolunteer = User::role('user')->where('na_id', $naHead->na_id)->firstOrFail();
        $otherNaVolunteer = User::role('user')->where('na_id', '!=', $naHead->na_id)->firstOrFail();

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

    public function test_uc_head_only_sees_their_own_uc(): void
    {
        $ucHead = User::where('email', 'uchead1@example.com')->first();
        $ucIds = $ucHead->ucsHeaded->pluck('id');

        $ownUcVolunteer = User::role('user')->whereIn('uc_id', $ucIds)->firstOrFail();
        $otherUcVolunteer = User::role('user')->whereNotIn('uc_id', $ucIds)->firstOrFail();

        $ownReport = $this->actingAs($ownUcVolunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-21', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $otherReport = $this->actingAs($otherUcVolunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-21', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($ucHead, 'sanctum')->getJson("/api/reports/{$ownReport}")->assertOk();
        $this->actingAs($ucHead, 'sanctum')->getJson("/api/reports/{$otherReport}")->assertForbidden();
    }

    public function test_super_admin_sees_all_nas(): void
    {
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        $volunteer = User::role('user')->firstOrFail();

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-22', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($superAdmin, 'sanctum')->getJson("/api/reports/{$reportId}")->assertOk();
    }
}
