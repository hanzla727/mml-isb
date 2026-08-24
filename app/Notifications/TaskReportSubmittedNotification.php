<?php

namespace App\Notifications;

use App\Models\TaskReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to reviewers (users with review-task-reports) when a volunteer submits
 * or resubmits a task report.
 */
class TaskReportSubmittedNotification extends Notification
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
            'submitted_by' => $this->report->user->name,
            'message' => "{$this->report->user->name} submitted a report for review.",
        ];
    }
}
