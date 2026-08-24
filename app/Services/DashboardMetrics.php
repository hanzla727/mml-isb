<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\DailyReport;
use App\Models\LeaveRequest;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardMetrics
{
    public function forUser(User $user): array
    {
        $today = Carbon::today();
        $todayReport = $user->dailyReports()->whereDate('report_date', $today)->with('meetings')->first();

        $monthStart = $today->copy()->startOfMonth();
        $monthlyHours = $user->dailyReports()
            ->whereBetween('report_date', [$monthStart, $today])
            ->sum('total_hours');
        $monthlyMeetings = Meeting::whereHas('dailyReport', fn ($q) => $q->where('user_id', $user->id)
            ->whereBetween('report_date', [$monthStart, $today])
        )->count();

        $myTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id));

        return [
            'today' => [
                'has_submitted' => (bool) $todayReport,
                'hours_worked' => $todayReport?->total_hours ?? 0,
                'meetings_count' => $todayReport?->meetings->count() ?? 0,
            ],
            'monthly' => [
                'total_hours' => (float) $monthlyHours,
                'total_meetings' => $monthlyMeetings,
            ],
            'targets_count' => $user->targetProgressUpdates()->distinct('target_id')->count('target_id'),
            'meetings' => [
                'upcoming' => $user->scheduledMeetingsParticipating()->where('meeting_date', '>=', $today)->count(),
                'today' => $user->scheduledMeetingsParticipating()->whereDate('meeting_date', $today)->count(),
            ],
            'tasks' => [
                'today' => (clone $myTasks)->whereDate('due_date', $today)->count(),
                'pending' => (clone $myTasks)->whereIn('status', Task::OPEN_STATUSES)->count(),
                'completed' => (clone $myTasks)->whereIn('status', ['completed', 'approved', 'closed'])->count(),
                'rejected' => (clone $myTasks)->where('status', 'rejected')->count(),
                'needs_revision' => (clone $myTasks)->where('status', 'needs_revision')->count(),
                'recently_approved' => TaskReport::where('user_id', $user->id)
                    ->whereIn('review_status', ['approved', 'approved_with_remarks'])
                    ->orderByDesc('reviewed_at')
                    ->limit(5)
                    ->count(),
            ],
            'leave_status' => [
                'pending' => $user->leaveRequests()->where('status', 'pending')->count(),
                'upcoming_approved' => $user->leaveRequests()->where('status', 'approved')->where('end_date', '>=', $today)->count(),
            ],
            'assigned_projects' => Project::whereHas('tasks', fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $user->id)))
                ->orWhereHas('meetings', fn ($q) => $q->whereHas('participants', fn ($q2) => $q2->where('users.id', $user->id)))
                ->distinct()
                ->get(['id', 'name', 'status']),
        ];
    }

    /**
     * $viewer determines the hierarchy scope: Super Admin sees everything,
     * Admin sees their assigned PPs, PP Head sees their one PP, Team Leader
     * sees their one Team.
     */
    public function forAdmin(User $viewer): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $yearStart = $today->copy()->startOfYear();

        $visibleIds = HierarchyScope::visibleUserIds($viewer);
        $visibleUcIds = HierarchyScope::visibleUcIds($viewer);

        $totalUsers = User::role('user')->when($visibleIds !== null, fn ($q) => $q->whereIn('id', $visibleIds))->count();
        $todaysReportsUserIds = DailyReport::whereDate('report_date', $today)
            ->when($visibleIds !== null, fn ($q) => $q->whereIn('user_id', $visibleIds))
            ->pluck('user_id');

        return [
            'total_users' => $totalUsers,
            'today' => [
                'reports_submitted' => $todaysReportsUserIds->count(),
                'pending_reports' => max(0, $totalUsers - $todaysReportsUserIds->count()),
                'total_hours' => (float) $this->reportsQuery($visibleIds)->whereDate('report_date', $today)->sum('total_hours'),
                'total_meetings' => $this->fieldMeetingsQuery($visibleIds, $today, $today)->count(),
                'new_contacts' => Contact::whereDate('created_at', $today)->count(),
            ],
            'weekly' => [
                'total_hours' => (float) $this->reportsQuery($visibleIds)->whereBetween('report_date', [$weekStart, $today])->sum('total_hours'),
                'total_meetings' => $this->fieldMeetingsQuery($visibleIds, $weekStart, $today)->count(),
            ],
            'monthly' => [
                'total_hours' => (float) $this->reportsQuery($visibleIds)->whereBetween('report_date', [$monthStart, $today])->sum('total_hours'),
                'total_meetings' => $this->fieldMeetingsQuery($visibleIds, $monthStart, $today)->count(),
            ],
            'yearly' => [
                'total_hours' => (float) $this->reportsQuery($visibleIds)->whereBetween('report_date', [$yearStart, $today])->sum('total_hours'),
                'total_meetings' => $this->fieldMeetingsQuery($visibleIds, $yearStart, $today)->count(),
            ],
            'meetings' => [
                'upcoming' => $this->scheduledMeetingsQuery($visibleIds)->where('meeting_date', '>=', $today)->count(),
                'today' => $this->scheduledMeetingsQuery($visibleIds)->whereDate('meeting_date', $today)->count(),
                'attendance_rate' => $this->meetingAttendanceRate($visibleIds),
            ],
            'tasks' => [
                'total_assigned' => $this->tasksQuery($visibleIds)->count(),
                'overdue' => $this->tasksQuery($visibleIds)->overdue()->count(),
                'due_today' => $this->tasksQuery($visibleIds)->whereDate('due_date', $today)->count(),
                'completion_rate' => $this->taskCompletionRate($visibleIds),
            ],
            'reports' => [
                'pending' => $this->taskReportsQuery($visibleIds)->whereIn('review_status', ['pending', 'under_review', 're_submitted'])->count(),
                'awaiting_review' => $this->taskReportsQuery($visibleIds)->where('review_status', 'under_review')->count(),
                'approved' => $this->taskReportsQuery($visibleIds)->whereIn('review_status', ['approved', 'approved_with_remarks'])->count(),
                'rejected' => $this->taskReportsQuery($visibleIds)->where('review_status', 'rejected')->count(),
            ],
            'volunteers_on_leave' => LeaveRequest::where('status', 'approved')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->when($visibleIds !== null, fn ($q) => $q->whereIn('user_id', $visibleIds))
                ->count(),
            'active_projects' => Project::where('status', 'active')
                ->when($visibleUcIds !== null, fn ($q) => $q->whereIn('uc_id', $visibleUcIds))
                ->withCount('tasks')
                ->get(['id', 'name', 'department_id'])
                ->map(fn (Project $project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'progress' => $project->progress(),
                ]),
            'recent_reports' => $this->reportsQuery($visibleIds)->with('user')->orderByDesc('report_date')->limit(5)->get(),
        ];
    }

    private function reportsQuery(?array $visibleIds)
    {
        return DailyReport::query()->when($visibleIds !== null, fn ($q) => $q->whereIn('user_id', $visibleIds));
    }

    /**
     * $from/$to filter on the related daily_reports.report_date, since
     * meetings themselves have no date column of their own.
     */
    private function fieldMeetingsQuery(?array $visibleIds, ?Carbon $from = null, ?Carbon $to = null)
    {
        return Meeting::whereHas('dailyReport', function ($q) use ($visibleIds, $from, $to) {
            $q->when($visibleIds !== null, fn ($q2) => $q2->whereIn('user_id', $visibleIds));

            if ($from !== null && $to !== null) {
                $q->whereBetween('report_date', [$from, $to]);
            }
        });
    }

    private function scheduledMeetingsQuery(?array $visibleIds)
    {
        return ScheduledMeeting::query()->when(
            $visibleIds !== null,
            fn ($q) => $q->whereHas('participants', fn ($q2) => $q2->whereIn('users.id', $visibleIds))
        );
    }

    private function tasksQuery(?array $visibleIds)
    {
        return Task::query()->when(
            $visibleIds !== null,
            fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->whereIn('users.id', $visibleIds))
        );
    }

    private function taskReportsQuery(?array $visibleIds)
    {
        return TaskReport::query()->when($visibleIds !== null, fn ($q) => $q->whereIn('user_id', $visibleIds));
    }

    private function taskCompletionRate(?array $visibleIds): float
    {
        $total = $this->tasksQuery($visibleIds)->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->tasksQuery($visibleIds)->whereIn('status', ['completed', 'approved', 'closed'])->count();

        return round(($completed / $total) * 100, 1);
    }

    private function meetingAttendanceRate(?array $visibleIds): float
    {
        $query = DB::table('meeting_attendances')
            ->when($visibleIds !== null, fn ($q) => $q->whereIn('user_id', $visibleIds));

        $totalMarked = (clone $query)->count();
        if ($totalMarked === 0) {
            return 0;
        }

        $attended = (clone $query)->whereIn('status', ['present', 'late'])->count();

        return round(($attended / $totalMarked) * 100, 1);
    }
}
