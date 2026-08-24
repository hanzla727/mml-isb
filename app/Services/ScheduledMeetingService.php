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
        return DB::transaction(function () use ($creator, $data) {
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

            $this->syncParticipants($meeting, $data);
            $this->notifyParticipants($meeting, new MeetingCreatedNotification($meeting));

            if ($meeting->recurrence_rule) {
                $this->recurrenceGenerator->generateUpcoming($meeting);
            }

            return $meeting;
        });
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

    public function update(ScheduledMeeting $meeting, array $data): ScheduledMeeting
    {
        return DB::transaction(function () use ($meeting, $data) {
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

            if (isset($data['scope'])) {
                $this->syncParticipants($meeting, $data);
            }

            $this->notifyParticipants($meeting, new MeetingUpdatedNotification($meeting));

            return $meeting;
        });
    }

    private function syncParticipants(ScheduledMeeting $meeting, array $data): void
    {
        $userIds = AudienceResolver::resolve($data['scope'] ?? 'individual', $data);

        $meeting->participants()->sync(array_fill_keys($userIds, ['notified_at' => now()]));
    }

    private function notifyParticipants(ScheduledMeeting $meeting, $notification): void
    {
        $participants = $meeting->participants()->get();

        if ($participants->isNotEmpty()) {
            Notification::send($participants, $notification);
        }
    }
}
