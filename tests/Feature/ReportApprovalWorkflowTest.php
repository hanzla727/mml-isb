<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ReportStatusChangedNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReportApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_report_from_volunteer_with_team_leader_goes_to_pending_review_first(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first(); // on Donor Relations Team, has a leader.

        $report = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-25', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ]);

        $report->assertCreated();
        $report->assertJsonPath('data.review_status', 'pending_review');
    }

    public function test_report_from_volunteer_without_team_leader_skips_to_under_review(): void
    {
        $volunteer = User::where('email', 'volunteer3@example.com')->first(); // Hospital dept, no team leader assigned.

        $report = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-25', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ]);

        $report->assertCreated();
        $report->assertJsonPath('data.review_status', 'under_review');
    }

    public function test_full_chain_team_leader_recommends_then_admin_approves_and_volunteer_is_notified(): void
    {
        Notification::fake();

        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $teamLeader = User::where('email', 'teamleader1@example.com')->first();
        $admin = User::where('email', 'admin1@example.com')->first(); // assigned to volunteer1's NA (NA-48).

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-25', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        // Team leader recommends approval.
        $tlReview = $this->actingAs($teamLeader, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'recommend_approve',
            'remarks' => 'Looks good.',
        ]);
        $tlReview->assertOk();
        $tlReview->assertJsonPath('data.review_status', 'under_review');

        // Admin gives final approval.
        $adminReview = $this->actingAs($admin, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'approve',
        ]);
        $adminReview->assertOk();
        $adminReview->assertJsonPath('data.review_status', 'approved');

        Notification::assertSentTo($volunteer, ReportStatusChangedNotification::class);
    }

    public function test_rejection_by_team_leader_sends_to_revision_and_resubmission_routes_back_to_pending_review(): void
    {
        Notification::fake();

        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $teamLeader = User::where('email', 'teamleader1@example.com')->first();

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-26', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($teamLeader, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'needs_revision',
            'remarks' => 'Please add more detail.',
        ])->assertOk()->assertJsonPath('data.review_status', 'needs_revision');

        Notification::assertSentTo($volunteer, ReportStatusChangedNotification::class);

        $resubmit = $this->actingAs($volunteer, 'sanctum')->putJson("/api/reports/{$reportId}", [
            'report_date' => '2026-08-26', 'field_start_time' => '09:00', 'field_end_time' => '18:00',
            'status' => 'submitted', 'summary' => 'Added more detail.',
        ]);

        $resubmit->assertOk();
        $resubmit->assertJsonPath('data.review_status', 'pending_review');
    }

    public function test_team_leader_from_a_different_team_cannot_review_the_report(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        // Promote volunteer3 (different team, no team assigned as leader) to team_leader role
        // to prove the check is about the specific team_leader_id snapshot, not just the role.
        $otherLeader = User::where('email', 'volunteer3@example.com')->first();
        $otherLeader->assignRole('team_leader');

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-27', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($otherLeader, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'recommend_approve',
        ])->assertForbidden();
    }
}
