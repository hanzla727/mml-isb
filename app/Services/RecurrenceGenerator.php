<?php

namespace App\Services;

use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Notifications\MeetingCreatedNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class RecurrenceGenerator
{
    /**
     * Generate concrete occurrences for a recurring ScheduledMeeting or Task
     * template up to $horizonDays out (or the rule's "until" date, whichever
     * is sooner). Generated rows are plain records with recurring_parent_id
     * set but no recurrence_rule of their own, so they never generate further
     * occurrences themselves — only the original template does.
     */
    public function generateUpcoming(ScheduledMeeting|Task $template, int $horizonDays = 60): Collection
    {
        $rule = $template->recurrence_rule;
        if (! $rule || empty($rule['frequency'])) {
            return collect();
        }

        $anchor = $template instanceof ScheduledMeeting ? $template->meeting_date : $template->due_date;
        if (! $anchor) {
            return collect();
        }

        $existingDates = $template->recurrences()
            ->pluck($template instanceof ScheduledMeeting ? 'meeting_date' : 'due_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();

        $dates = $this->occurrenceDates(Carbon::parse($anchor), $rule, $horizonDays);

        return collect($dates)
            ->reject(fn (Carbon $date) => in_array($date->toDateString(), $existingDates, true))
            ->map(fn (Carbon $date) => $template instanceof ScheduledMeeting
                ? $this->createMeetingInstance($template, $date)
                : $this->createTaskInstance($template, $date))
            ->values();
    }

    private function occurrenceDates(Carbon $anchor, array $rule, int $horizonDays): array
    {
        $until = ! empty($rule['until']) ? Carbon::parse($rule['until']) : null;
        $horizon = Carbon::today()->addDays($horizonDays);
        $limit = $until && $until->lt($horizon) ? $until : $horizon;

        $dates = [];
        $current = $anchor->copy();

        while (count($dates) < 500) {
            $current = $this->step($current, $rule);
            if ($current->gt($limit)) {
                break;
            }
            $dates[] = $current->copy();
        }

        return $dates;
    }

    private function step(Carbon $date, array $rule): Carbon
    {
        $interval = max(1, (int) ($rule['interval'] ?? 1));

        return match ($rule['frequency']) {
            'daily' => $date->copy()->addDays($interval),
            'monthly' => $date->copy()->addMonths($interval),
            default => $date->copy()->addWeeks($interval),
        };
    }

    private function createMeetingInstance(ScheduledMeeting $template, Carbon $date): ScheduledMeeting
    {
        $instance = ScheduledMeeting::create([
            'project_id' => $template->project_id,
            'title' => $template->title,
            'type' => $template->type,
            'meeting_date' => $date->toDateString(),
            'start_time' => $template->start_time,
            'end_time' => $template->end_time,
            'location' => $template->location,
            'description' => $template->description,
            'agenda' => $template->agenda,
            'organizer_id' => $template->organizer_id,
            'status' => 'upcoming',
            'created_by' => $template->created_by,
            'recurring_parent_id' => $template->id,
        ]);

        $participantIds = $template->participants()->pluck('users.id')->all();
        if (! empty($participantIds)) {
            $instance->participants()->sync(array_fill_keys($participantIds, ['notified_at' => now()]));
            Notification::send($instance->participants, new MeetingCreatedNotification($instance));
        }

        return $instance;
    }

    private function createTaskInstance(Task $template, Carbon $date): Task
    {
        $instance = Task::create([
            'scheduled_meeting_id' => $template->scheduled_meeting_id,
            'project_id' => $template->project_id,
            'title' => $template->title,
            'description' => $template->description,
            'priority' => $template->priority,
            'due_date' => $date->toDateString(),
            'due_time' => $template->due_time,
            'status' => 'not_assigned',
            'notes' => $template->notes,
            'created_by' => $template->created_by,
            'recurring_parent_id' => $template->id,
        ]);

        $assigneeIds = $template->assignees()->pluck('users.id')->all();
        if (! empty($assigneeIds)) {
            $instance->assignees()->sync($assigneeIds);
            $instance->update(['status' => 'assigned']);
            Notification::send($instance->assignees, new TaskAssignedNotification($instance));
        }

        return $instance;
    }
}
