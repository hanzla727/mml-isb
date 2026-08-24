<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        return $meeting->dailyReport->user_id === $user->id
            || $meeting->isParticipant($user)
            || $user->can('view-reports');
    }
}
