<?php

namespace App\Notifications;

use App\Models\TaskReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReportApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TaskReport $report)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'task_report_id' => $this->report->id,
            'task_id' => $this->report->task_id,
            'remarks' => $this->report->review_remarks,
            'message' => 'Your task report has been approved.',
        ];
    }
}
