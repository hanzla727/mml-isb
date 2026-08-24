<?php

namespace App\Notifications;

use App\Models\DailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly DailyReport $report, private readonly ?string $remarks = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'daily_report_id' => $this->report->id,
            'report_date' => $this->report->report_date->toDateString(),
            'review_status' => $this->report->review_status,
            'remarks' => $this->remarks,
            'message' => "Your report for {$this->report->report_date->toDateString()} is now: ".str_replace('_', ' ', $this->report->review_status).'.',
        ];
    }
}
