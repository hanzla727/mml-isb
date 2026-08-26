<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ReportStatusChangedNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
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

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_report_from_any_volunteer_goes_straight_to_under_review(): void
    {
        $volunteer = User::role('user')->firstOrFail();

        $report = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-25', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ]);

        $report->assertCreated();
        $report->assertJsonPath('data.review_status', 'under_review');
    }

    public function test_na_head_approves_and_volunteer_is_notified(): void
    {
        Notification::fake();

        $naHead = User::where('email', 'nahead1@example.com')->first(); // heads NA-48.
        $volunteer = User::role('user')->where('na_id', $naHead->na_id)->firstOrFail();

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-25', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $review = $this->actingAs($naHead, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'approve',
        ]);
        $review->assertOk();
        $review->assertJsonPath('data.review_status', 'approved');

        Notification::assertSentTo($volunteer, ReportStatusChangedNotification::class);
    }

    public function test_rejection_sends_to_revision_and_resubmission_routes_back_to_under_review(): void
    {
        Notification::fake();

        $naHead = User::where('email', 'nahead1@example.com')->first();
        $volunteer = User::role('user')->where('na_id', $naHead->na_id)->firstOrFail();

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-26', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($naHead, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'needs_revision',
            'remarks' => 'Please add more detail.',
        ])->assertOk()->assertJsonPath('data.review_status', 'needs_revision');

        Notification::assertSentTo($volunteer, ReportStatusChangedNotification::class);

        $resubmit = $this->actingAs($volunteer, 'sanctum')->putJson("/api/reports/{$reportId}", [
            'report_date' => '2026-08-26', 'field_start_time' => '09:00', 'field_end_time' => '18:00',
            'status' => 'submitted', 'summary' => 'Added more detail.',
        ]);

        $resubmit->assertOk();
        $resubmit->assertJsonPath('data.review_status', 'under_review');
    }

    public function test_head_from_a_different_na_cannot_review_the_report(): void
    {
        $naHead = User::where('email', 'nahead1@example.com')->first(); // heads NA-48.
        $volunteer = User::role('user')->where('na_id', $naHead->na_id)->firstOrFail();
        $otherNaHead = User::where('email', 'nahead2@example.com')->first(); // heads NA-49.

        $reportId = $this->actingAs($volunteer, 'sanctum')->postJson('/api/reports', [
            'report_date' => '2026-08-27', 'field_start_time' => '09:00', 'field_end_time' => '17:00', 'status' => 'submitted',
        ])->json('data.id');

        $this->actingAs($otherNaHead, 'sanctum')->putJson("/api/reports/{$reportId}/review", [
            'decision' => 'approve',
        ])->assertForbidden();
    }
}
