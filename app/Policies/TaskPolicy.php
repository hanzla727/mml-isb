<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->can('manage-tasks') || $task->isAssignee($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-tasks');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('manage-tasks');
    }

    public function submitReport(User $user, Task $task): bool
    {
        return $task->isAssignee($user) && $user->can('submit-task-reports');
    }
}
