<?php

namespace App\Policies;

use App\Models\TaskReport;
use App\Models\User;
use App\Services\HierarchyScope;

class TaskReportPolicy
{
    public function view(User $user, TaskReport $report): bool
    {
        if ($report->user_id === $user->id) {
            return true;
        }

        return $user->can('review-task-reports') && HierarchyScope::canView($user, $report->user);
    }

    public function review(User $user, TaskReport $report): bool
    {
        return $user->can('review-task-reports') && HierarchyScope::canView($user, $report->user);
    }
}
