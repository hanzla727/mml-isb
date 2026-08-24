<?php

namespace App\Notifications;

use App\Models\ScheduledMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ScheduledMeeting $meeting)
    {
    }

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
            'message' => "Reminder: \"{$this->meeting->title}\" starts on {$this->meeting->meeting_date->toDateString()} at {$this->meeting->start_time}.",
        ];
    }
}
