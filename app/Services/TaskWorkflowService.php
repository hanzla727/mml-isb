<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskReport;
use App\Models\User;
use App\Notifications\NewTaskCommentNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskNeedsRevisionNotification;
use App\Notifications\TaskReportApprovedNotification;
use App\Notifications\TaskReportRejectedNotification;
use App\Notifications\TaskReportSubmittedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TaskWorkflowService
{
    public function __construct(private RecurrenceGenerator $recurrenceGenerator) {}

    /**
     * Reviewers are everyone holding review-task-reports — small org, so a
     * direct permission lookup is simpler and safer to keep in sync than a
     * hand-maintained reviewer list.
     */
    private function reviewers()
    {
        return User::permission('review-task-reports')->get();
    }

    public function create(User $creator, array $data): Task
    {
        return DB::transaction(function () use ($creator, $data) {
            $task = Task::create([
                'scheduled_meeting_id' => $data['scheduled_meeting_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'form_template_id' => $data['form_template_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'due_date' => $data['due_date'] ?? null,
                'due_time' => $data['due_time'] ?? null,
                'status' => 'not_assigned',
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
                'recurrence_rule' => $this->buildRecurrenceRule($data),
            ]);

            $this->assign($task, $data, $creator);

            if ($task->recurrence_rule) {
                $this->recurrenceGenerator->generateUpcoming($task->fresh());
            }

            return $task;
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

    public function assign(Task $task, array $data, ?User $creator = null): Task
    {
        $userIds = $creator ? $this->resolveAudience($creator, $data) : AudienceResolver::resolve($data['scope'] ?? 'individual', $data);

        $task->assignees()->sync($userIds);
        $task->update(['status' => empty($userIds) ? 'not_assigned' : 'assigned']);

        if (! empty($userIds)) {
            Notification::send(User::whereIn('id', $userIds)->get(), new TaskAssignedNotification($task));
        }

        return $task->fresh();
    }

    /**
     * Restricts the resolved audience to whatever HierarchyScope says the
     * creator can actually reach — mirrors
     * ScheduledMeetingService::resolveAudience() so a Team Leader/NA
     * Head/UC Head/Admin can't assign a task outside their own scope no
     * matter which scope value they submit. Super Admin is unrestricted
     * (HierarchyScope::visibleUserIds returns null for them).
     */
    private function resolveAudience(User $creator, array $data): array
    {
        $visibleIds = HierarchyScope::visibleUserIds($creator);
        $userIds = AudienceResolver::resolve($data['scope'] ?? 'individual', $data);

        if ($visibleIds === null) {
            return $userIds;
        }

        // "All Volunteers" has no specific target to be outside of — for a
        // scoped creator it means "everyone I have authority over", the
        // same as my_scope elsewhere, rather than a hard block. Every other
        // scope (team/department/uc/na/...) names a specific target, so
        // reaching outside it is a real authorization violation.
        if (($data['scope'] ?? null) === 'all') {
            return array_values(array_intersect($userIds, $visibleIds));
        }

        abort_unless(
            empty(array_diff($userIds, $visibleIds)),
            403,
            'You can only assign this task to people within your own scope.'
        );

        return $userIds;
    }

    public function submitReport(Task $task, User $user, array $data): TaskReport
    {
        return DB::transaction(function () use ($task, $user, $data) {
            $nextVersion = $task->reports()->where('user_id', $user->id)->max('version') + 1;

            $report = TaskReport::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'work_summary' => $data['work_summary'] ?? null,
                'description' => $data['description'] ?? null,
                'achievements' => $data['achievements'] ?? null,
                'problems_faced' => $data['problems_faced'] ?? null,
                'next_plan' => $data['next_plan'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'amount_collected' => $data['amount_collected'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'review_status' => $nextVersion > 1 ? 're_submitted' : 'pending',
                'version' => $nextVersion,
                'submitted_at' => now(),
            ]);

            $task->update(['status' => 'report_submitted']);

            Notification::send($this->reviewers(), new TaskReportSubmittedNotification($report));

            return $report;
        });
    }

    public function review(TaskReport $report, User $reviewer, string $decision, ?string $remarks = null): TaskReport
    {
        [$reviewStatus, $taskStatus] = match ($decision) {
            'approve' => ['approved', 'approved'],
            'approve_with_remarks' => ['approved_with_remarks', 'approved'],
            'reject' => ['rejected', 'rejected'],
            'return_for_revision' => ['needs_revision', 'needs_revision'],
            'request_more_information' => ['under_review', 'waiting_for_information'],
            default => ['under_review', 'under_review'],
        };

        return DB::transaction(function () use ($report, $reviewer, $reviewStatus, $taskStatus, $remarks) {
            $report->update([
                'review_status' => $reviewStatus,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_remarks' => $remarks,
            ]);

            $report->task->update(['status' => $taskStatus]);

            $notification = match ($reviewStatus) {
                'approved', 'approved_with_remarks' => new TaskReportApprovedNotification($report),
                'rejected' => new TaskReportRejectedNotification($report),
                'needs_revision' => new TaskNeedsRevisionNotification($report),
                default => null,
            };

            if ($notification) {
                $report->user->notify($notification);
            }

            return $report->fresh();
        });
    }

    public function addComment(Task $task, User $author, string $body): TaskComment
    {
        $comment = $task->comments()->create(['user_id' => $author->id, 'body' => $body]);

        $notifyUsers = $task->assignees()->where('users.id', '!=', $author->id)->get()
            ->merge($this->reviewers()->filter(fn (User $u) => $u->id !== $author->id));

        Notification::send($notifyUsers->unique('id'), new NewTaskCommentNotification($comment));

        return $comment;
    }
}
