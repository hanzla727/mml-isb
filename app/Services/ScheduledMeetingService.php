<?php

namespace App\Services;

use App\Models\ScheduledMeeting;
use App\Models\User;
use App\Notifications\MeetingCreatedNotification;
use App\Notifications\MeetingUpdatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ScheduledMeetingService
{
    public function __construct(private RecurrenceGenerator $recurrenceGenerator) {}

    public function create(User $creator, array $data): ScheduledMeeting
    {
        $userIds = $this->resolveAudience($creator, $data);

        return DB::transaction(function () use ($creator, $data, $userIds) {
            $meeting = ScheduledMeeting::create([
                'project_id' => $data['project_id'] ?? null,
                'form_template_id' => $data['form_template_id'] ?? null,
                'title' => $data['title'],
                'type' => $data['type'] ?? 'general',
                'meeting_date' => $data['meeting_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
                'agenda' => $data['agenda'] ?? null,
                'organizer_id' => $data['organizer_id'] ?? $creator->id,
                'status' => 'upcoming',
                'created_by' => $creator->id,
                'recurrence_rule' => $this->buildRecurrenceRule($data),
            ]);

            $meeting->participants()->sync(array_fill_keys($userIds, ['notified_at' => now()]));
            $this->notifyParticipants($meeting, new MeetingCreatedNotification($meeting));

            if ($meeting->recurrence_rule) {
                $this->recurrenceGenerator->generateUpcoming($meeting);
            }

            return $meeting;
        });
    }

    /**
     * `scope: 'my_scope'` means "resolve to whoever this creator's role
     * already lets them see" — NA Head's NA, UC Head's UC(s), Admin's
     * assigned NAs — read straight from HierarchyScope (the same source
     * every other visibility check in the app uses) rather than needing the
     * client to pick and send a specific na_id/uc_id. This is what the
     * mobile app's "create meeting" screen uses for every role except
     * Admin/Super Admin (who pick 'all').
     *
     * For every other scope value ('department', 'na', 'individual', ...),
     * the resolved audience is still cross-checked against
     * HierarchyScope so a Team Leader/NA Head/UC Head/Admin can't reach
     * outside their own scope no matter which scope value they submit.
     * Super Admin is unrestricted (HierarchyScope::visibleUserIds returns
     * null for them).
     */
    private function resolveAudience(User $creator, array $data): array
    {
        $visibleIds = HierarchyScope::visibleUserIds($creator);

        if (($data['scope'] ?? null) === 'my_scope') {
            $pool = $visibleIds ?? User::where('is_active', true)->pluck('id')->all();

            return array_values(array_diff($pool, [$creator->id]));
        }

        $userIds = AudienceResolver::resolve($data['scope'] ?? 'individual', $data);

        if ($visibleIds !== null) {
            // "All Volunteers" has no specific target to be outside of — for
            // a scoped creator it means "everyone I have authority over",
            // the same as :my_scope, rather than a hard block. Every other
            // scope (team/department/uc/na/...) names a specific target, so
            // reaching outside it is a real authorization violation.
            if (($data['scope'] ?? null) === 'all') {
                return array_values(array_intersect($userIds, $visibleIds));
            }

            abort_unless(
                empty(array_diff($userIds, $visibleIds)),
                403,
                'You can only invite people within your own scope to a meeting.'
            );
        }

        return $userIds;
    }

    private function buildRecurrenceRule(array $data): ?array
    {
        if (empty($data['is_recurring']) || empty($data['recurrence_frequency'])) {
            return null;
        }

        return [
            'frequency' => $data['recurrence_frequency'],
            'interval' => (int) ($data['recurrence_interval'] ?? 1),
            'until' => $data['recurrence_until'] ?? null,
        ];
    }

    public function update(User $editor, ScheduledMeeting $meeting, array $data): ScheduledMeeting
    {
        $userIds = isset($data['scope']) ? $this->resolveAudience($editor, $data) : null;

        return DB::transaction(function () use ($meeting, $data, $userIds) {
            $meeting->update([
                'project_id' => $data['project_id'] ?? $meeting->project_id,
                'title' => $data['title'],
                'type' => $data['type'] ?? $meeting->type,
                'meeting_date' => $data['meeting_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
                'agenda' => $data['agenda'] ?? null,
                'organizer_id' => $data['organizer_id'] ?? $meeting->organizer_id,
                'status' => $data['status'] ?? $meeting->status,
            ]);

            if ($userIds !== null) {
                $meeting->participants()->sync(array_fill_keys($userIds, ['notified_at' => now()]));
            }

            $this->notifyParticipants($meeting, new MeetingUpdatedNotification($meeting));

            return $meeting;
        });
    }

    private function notifyParticipants(ScheduledMeeting $meeting, $notification): void
    {
        $participants = $meeting->participants()->get();

        if ($participants->isNotEmpty()) {
            Notification::send($participants, $notification);
        }
    }
}
