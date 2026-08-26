<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\ReportStatusChangedNotification;

class ReportApprovalService
{
    /**
     * Called right after a report is submitted (not saved as draft). Single
     * review stage — whoever holds review-reports and can see this
     * volunteer via HierarchyScope (their UC Head, NA Head, or Admin) can
     * act on it directly, no separate team-leader stage.
     */
    public function submit(DailyReport $report): DailyReport
    {
        $report->update(['review_status' => 'under_review']);

        return $report->fresh();
    }

    public function review(DailyReport $report, User $reviewer, string $decision, ?string $remarks = null): DailyReport
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
            'admin_reviewed_by' => $reviewer->id,
            'admin_reviewed_at' => now(),
            'admin_remarks' => $remarks,
        ]);

        $report->user->notify(new ReportStatusChangedNotification($report->fresh(), $remarks));

        return $report->fresh();
    }

    /**
     * A volunteer resubmitting after "needs_revision" — routes straight
     * back into the same single review stage, marked as a resubmission.
     */
    public function resubmit(DailyReport $report): DailyReport
    {
        $report->update(['review_status' => 'under_review']);

        return $report->fresh();
    }
}
