<?php

namespace App\Notifications;

use App\Models\ScheduledMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ScheduledMeeting $meeting)
    {
    }

    // Email channel intentionally omitted for now (spec marks email "optional"
    // and no mail infrastructure/templates exist yet) — add 'mail' here later.
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'scheduled_meeting_id' => $this->meeting->id,
            'title' => $this->meeting->title,
            'meeting_date' => $this->meeting->meeting_date->toDateString(),
            'message' => "You've been added to the meeting \"{$this->meeting->title}\" on {$this->meeting->meeting_date->toDateString()}.",
        ];
    }
}
