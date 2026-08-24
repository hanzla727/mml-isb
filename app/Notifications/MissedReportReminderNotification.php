<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class MissedReportReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Carbon $date)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'report_date' => $this->date->toDateString(),
            'message' => "You haven't submitted your daily report for {$this->date->toDateString()} yet.",
        ];
    }
}
