<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Meeting $meeting)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $creatorName = $this->meeting->dailyReport->user->name;
        $meetingDate = $this->meeting->meeting_datetime?->toDateString()
            ?? $this->meeting->dailyReport->report_date->toDateString();

        return [
            'meeting_id' => $this->meeting->id,
            'daily_report_id' => $this->meeting->daily_report_id,
            'title' => $this->meeting->title,
            'creator_name' => $creatorName,
            'meeting_datetime' => $this->meeting->meeting_datetime?->toISOString(),
            'message' => "{$creatorName} added you to a meeting on {$meetingDate}.",
        ];
    }
}
