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

    /**
     * Broader than 'manage-meetings' on purpose: Team Leaders can create
     * meetings for their own team even though they don't hold the
     * system-wide meeting-management permission. Who they're actually
     * allowed to invite is enforced separately, in
     * ScheduledMeetingService::assertCanTarget().
     */
    public function create(User $user): bool
    {
        return $user->can('manage-meetings') || $user->hasRole('team_leader');
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
