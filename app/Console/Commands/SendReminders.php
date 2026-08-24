<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\User;
use App\Notifications\LeaveDecisionPendingNotification;
use App\Notifications\MeetingReminderNotification;
use App\Notifications\MissedReportReminderNotification;
use App\Notifications\TaskDeadlineNearNotification;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send meeting, task, missed-report, and pending-leave reminder notifications';

    public function handle(): int
    {
        $sent = $this->sendMeetingReminders()
            + $this->sendTaskDeadlineReminders()
            + $this->sendMissedReportReminders()
            + $this->sendPendingLeaveReminders();

        $this->info("Sent {$sent} reminder notification(s).");

        return self::SUCCESS;
    }

    private function alreadyNotified(User $user, string $notificationClass, callable $dataMatch): bool
    {
        return DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->where('type', $notificationClass)
            ->whereDate('created_at', today())
            ->get()
            ->contains(fn ($row) => $dataMatch(json_decode($row->data, true)));
    }

    private function sendMeetingReminders(): int
    {
        $meetings = ScheduledMeeting::with('participants')
            ->whereBetween('meeting_date', [today(), today()->addDay()])
            ->where('status', 'upcoming')
            ->get();

        $count = 0;
        foreach ($meetings as $meeting) {
            foreach ($meeting->participants as $participant) {
                if ($this->alreadyNotified($participant, MeetingReminderNotification::class, fn ($data) => ($data['scheduled_meeting_id'] ?? null) === $meeting->id)) {
                    continue;
                }

                $participant->notify(new MeetingReminderNotification($meeting));
                $count++;
            }
        }

        return $count;
    }

    private function sendTaskDeadlineReminders(): int
    {
        $count = 0;

        $dueSoon = Task::with('assignees')
            ->whereDate('due_date', today()->addDay())
            ->whereIn('status', Task::OPEN_STATUSES)
            ->get();

        foreach ($dueSoon as $task) {
            foreach ($task->assignees as $assignee) {
                if ($this->alreadyNotified($assignee, TaskDeadlineNearNotification::class, fn ($data) => ($data['task_id'] ?? null) === $task->id)) {
                    continue;
                }

                $assignee->notify(new TaskDeadlineNearNotification($task));
                $count++;
            }
        }

        $overdue = Task::with('assignees')->overdue()->get();

        foreach ($overdue as $task) {
            foreach ($task->assignees as $assignee) {
                if ($this->alreadyNotified($assignee, TaskOverdueNotification::class, fn ($data) => ($data['task_id'] ?? null) === $task->id)) {
                    continue;
                }

                $assignee->notify(new TaskOverdueNotification($task));
                $count++;
            }
        }

        return $count;
    }

    private function sendMissedReportReminders(): int
    {
        $cutoffHour = config('reminders.report_cutoff_hour', 18);
        if (Carbon::now()->hour < $cutoffHour) {
            return 0;
        }

        $today = today();

        $submittedUserIds = DB::table('daily_reports')->whereDate('report_date', $today)->pluck('user_id');

        $volunteers = User::role(['user', 'team_leader'])
            ->where('is_active', true)
            ->whereNotIn('id', $submittedUserIds)
            ->get();

        $count = 0;
        foreach ($volunteers as $volunteer) {
            if ($this->alreadyNotified($volunteer, MissedReportReminderNotification::class, fn ($data) => ($data['report_date'] ?? null) === $today->toDateString())) {
                continue;
            }

            $volunteer->notify(new MissedReportReminderNotification($today));
            $count++;
        }

        return $count;
    }

    private function sendPendingLeaveReminders(): int
    {
        $pending = LeaveRequest::where('status', 'pending')
            ->where('created_at', '<=', now()->subDay())
            ->get();

        $count = 0;
        foreach ($pending as $leaveRequest) {
            $reviewers = LeaveRequest::reviewersFor($leaveRequest->user, 'manage-leave-requests');

            foreach ($reviewers as $reviewer) {
                if ($this->alreadyNotified($reviewer, LeaveDecisionPendingNotification::class, fn ($data) => ($data['leave_request_id'] ?? null) === $leaveRequest->id)) {
                    continue;
                }

                $reviewer->notify(new LeaveDecisionPendingNotification($leaveRequest));
                $count++;
            }
        }

        return $count;
    }
}
