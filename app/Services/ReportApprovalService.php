<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\ReportStatusChangedNotification;

class ReportApprovalService
{
    /**
     * Called right after a report is submitted (not saved as draft). Routes
     * it to the author's team leader if they have one, otherwise straight to
     * Admin review — a volunteer with no team leader shouldn't get stuck.
     */
    public function submit(DailyReport $report): DailyReport
    {
        $author = $report->user;
        $teamLeaderId = $author->team?->leader_id;

        $report->update([
            'review_status' => $teamLeaderId ? 'pending_review' : 'under_review',
            'team_leader_id' => $teamLeaderId,
        ]);

        return $report->fresh();
    }

    public function teamLeaderReview(DailyReport $report, User $leader, string $decision, ?string $remarks = null): DailyReport
    {
        $reviewStatus = match ($decision) {
            'recommend_approve' => 'under_review',
            'needs_revision' => 'needs_revision',
            default => $report->review_status,
        };

        $report->update([
            'review_status' => $reviewStatus,
            'team_leader_reviewed_by' => $leader->id,
            'team_leader_reviewed_at' => now(),
            'team_leader_remarks' => $remarks,
        ]);

        $report->user->notify(new ReportStatusChangedNotification($report->fresh(), $remarks));

        return $report->fresh();
    }

    public function adminReview(DailyReport $report, User $admin, string $decision, ?string $remarks = null): DailyReport
    {
        $reviewStatus = match ($decision) {
            'approve' => 'approved',
            'approve_with_remarks' => 'approved_with_remarks',
            'reject' => 'rejected',
            'needs_revision' => 'needs_revision',
            'close' => 'closed',
            default => $report->review_status,
        };

        $report->update([
            'review_status' => $reviewStatus,
            'admin_reviewed_by' => $admin->id,
            'admin_reviewed_at' => now(),
            'admin_remarks' => $remarks,
        ]);

        $report->user->notify(new ReportStatusChangedNotification($report->fresh(), $remarks));

        return $report->fresh();
    }

    /**
     * A volunteer resubmitting after "needs_revision" — routes back through
     * the same reviewer chain, marked as a resubmission.
     */
    public function resubmit(DailyReport $report): DailyReport
    {
        $report->update([
            'review_status' => $report->team_leader_id ? 'pending_review' : 'under_review',
        ]);

        return $report->fresh();
    }
}
