<?php

namespace App\Policies;

use App\Models\TaskReport;
use App\Models\User;

class TaskReportPolicy
{
    public function view(User $user, TaskReport $report): bool
    {
        return $user->can('review-task-reports') || $report->user_id === $user->id;
    }

    public function review(User $user, TaskReport $report): bool
    {
        return $user->can('review-task-reports');
    }
}
