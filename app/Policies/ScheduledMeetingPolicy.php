<?php

namespace App\Policies;

use App\Models\ScheduledMeeting;
use App\Models\User;

class ScheduledMeetingPolicy
{
    public function view(User $user, ScheduledMeeting $meeting): bool
    {
        return $user->can('manage-meetings')
            || $meeting->organizer_id === $user->id
            || $meeting->isParticipant($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-meetings');
    }

    public function update(User $user, ScheduledMeeting $meeting): bool
    {
        return $user->can('manage-meetings') || $meeting->organizer_id === $user->id;
    }

    public function delete(User $user, ScheduledMeeting $meeting): bool
    {
        return $user->can('manage-meetings') || $meeting->organizer_id === $user->id;
    }
}
