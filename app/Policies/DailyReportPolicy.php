<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;
use App\Services\HierarchyScope;

class DailyReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-reports') || $user->can('review-reports') || $user->can('view-own-reports');
    }

    public function view(User $user, DailyReport $dailyReport): bool
    {
        if ($user->id === $dailyReport->user_id) {
            return true;
        }

        return ($user->can('view-reports') || $user->can('review-reports'))
            && HierarchyScope::canView($user, $dailyReport->user);
    }

    public function create(User $user): bool
    {
        return $user->can('submit-reports');
    }

    public function update(User $user, DailyReport $dailyReport): bool
    {
        if ($user->id !== $dailyReport->user_id) {
            return false;
        }

        // Same-day edits are always allowed; a report sent back for revision
        // can also be edited/resubmitted regardless of how many days the
        // review took, since review often doesn't happen same-day.
        return $dailyReport->report_date->isToday() || $dailyReport->review_status === 'needs_revision';
    }

    public function review(User $user, DailyReport $dailyReport): bool
    {
        return $user->can('review-reports')
            && HierarchyScope::canView($user, $dailyReport->user)
            && in_array($dailyReport->review_status, ['under_review', 're_submitted'], true);
    }
}
