<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\MeetingAttendance;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

class PerformanceEvaluator
{
    /**
     * Internal per-volunteer stats for a date range — for the volunteer's own
     * awareness and their reviewer's evaluation, not a public leaderboard.
     */
    public function summarize(User $user, Carbon $from, Carbon $to): array
    {
        $reports = DailyReport::where('user_id', $user->id)->whereBetween('report_date', [$from, $to]);
        $reportsTotal = (clone $reports)->count();
        $reportsApproved = (clone $reports)->whereIn('review_status', ['approved', 'approved_with_remarks'])->count();

        $attendances = MeetingAttendance::where('user_id', $user->id)
            ->whereHas('scheduledMeeting', fn ($q) => $q->whereBetween('meeting_date', [$from, $to]));
        $attendanceTotal = (clone $attendances)->count();
        $attendancePresent = (clone $attendances)->whereIn('status', ['present', 'late'])->count();

        $tasksAssigned = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->whereBetween('updated_at', [$from, $to]);
        $tasksCompleted = (clone $tasksAssigned)->whereIn('status', ['completed', 'approved', 'closed'])->count();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total_hours' => (float) (clone $reports)->sum('total_hours'),
            'reports' => ['submitted' => $reportsTotal, 'approved' => $reportsApproved],
            'meetings' => [
                'attended' => $attendancePresent,
                'marked' => $attendanceTotal,
                'attendance_rate' => $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : 0,
            ],
            'tasks' => ['completed' => $tasksCompleted, 'assigned' => (clone $tasksAssigned)->count()],
        ];
    }

    /**
     * Monthly breakdown of hours + reports for the last $months, oldest first — Chart.js-ready.
     */
    public function monthlyTrend(User $user, int $months = 6): array
    {
        $labels = [];
        $hours = [];
        $reportsSubmitted = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $labels[] = $monthStart->format('M Y');
            $hours[] = (float) DailyReport::where('user_id', $user->id)
                ->whereBetween('report_date', [$monthStart, $monthEnd])
                ->sum('total_hours');
            $reportsSubmitted[] = DailyReport::where('user_id', $user->id)
                ->whereBetween('report_date', [$monthStart, $monthEnd])
                ->count();
        }

        return ['labels' => $labels, 'hours' => $hours, 'reports' => $reportsSubmitted];
    }
}
