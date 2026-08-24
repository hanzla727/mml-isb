<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\ExpenseClaim;
use App\Models\Na;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * NA performance is a management tool, not gamification: every number here
 * is derived from real workflow data (reports, tasks, attendance, expenses)
 * rather than points/badges. score() produces a single configurable ranking
 * figure (see config/nas.php) so NAs can be compared like-for-like without a
 * leaderboard for individual volunteers.
 */
class NaPerformanceService
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function periodBounds(string $period): array
    {
        $today = Carbon::today();

        return match ($period) {
            'quarter' => [$today->copy()->subMonths(3), $today],
            'year' => [$today->copy()->startOfYear(), $today],
            default => [$today->copy()->subDays(30), $today],
        };
    }

    public function summarize(Na $na, Carbon $from, Carbon $to): array
    {
        $memberIds = User::where('na_id', $na->id)->pluck('id')->all();
        $volunteerIds = User::where('na_id', $na->id)->role('user')->pluck('id')->all();

        $totalVolunteers = count($volunteerIds);
        $activeVolunteers = User::where('na_id', $na->id)->role('user')->where('is_active', true)->count();

        $tasksQuery = fn () => Task::whereHas('assignees', fn ($q) => $q->whereIn('users.id', $memberIds))
            ->whereBetween('created_at', [$from, $to]);
        $tasksAssigned = (clone $tasksQuery())->count();
        $tasksCompleted = (clone $tasksQuery())->whereIn('status', ['completed', 'approved', 'closed'])->count();

        $reportsQuery = fn () => DailyReport::whereIn('user_id', $memberIds)->whereBetween('report_date', [$from, $to]);
        $reportsSubmitted = (clone $reportsQuery())->where('status', 'submitted')->count();
        $reportsPending = (clone $reportsQuery())->whereIn('review_status', ['pending_review', 'under_review', 're_submitted'])->count();
        $reportsApproved = (clone $reportsQuery())->whereIn('review_status', ['approved', 'approved_with_remarks'])->count();
        $volunteersWhoReported = (clone $reportsQuery())->where('status', 'submitted')->distinct('user_id')->count('user_id');

        $meetingsConducted = ScheduledMeeting::whereHas('participants', fn ($q) => $q->whereIn('users.id', $memberIds))
            ->whereBetween('meeting_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->count();

        $attendanceRate = $this->attendanceRate($memberIds, $from, $to);

        $fundCollection = (float) TaskReport::whereIn('user_id', $memberIds)
            ->whereBetween('submitted_at', [$from, $to])
            ->sum('amount_collected');

        $expenses = (float) ExpenseClaim::whereIn('user_id', $memberIds)
            ->where('status', 'approved')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $workingHours = (float) DailyReport::whereIn('user_id', $memberIds)
            ->whereBetween('report_date', [$from, $to])
            ->sum('total_hours');

        return [
            'total_volunteers' => $totalVolunteers,
            'active_volunteers' => $activeVolunteers,
            'meetings_conducted' => $meetingsConducted,
            'attendance_rate' => $attendanceRate,
            'tasks_assigned' => $tasksAssigned,
            'tasks_completed' => $tasksCompleted,
            'tasks_pending' => max(0, $tasksAssigned - $tasksCompleted),
            'reports_submitted' => $reportsSubmitted,
            'reports_pending' => $reportsPending,
            'reports_approved' => $reportsApproved,
            'volunteers_who_reported' => $volunteersWhoReported,
            'fund_collection' => $fundCollection,
            'expenses' => $expenses,
            'working_hours' => $workingHours,
            'hospital_activities' => $this->activityCount($na, 'Hospital', $from, $to),
            'mosque_activities' => $this->activityCount($na, 'Mosque', $from, $to),
            'khidmat_activities' => $this->activityCount($na, 'Khidmat', $from, $to),
            'events_completed' => $this->activityCount($na, 'Events', $from, $to),
        ];
    }

    /**
     * A single 0-100 composite figure for NA Ranking, built from
     * configurable weighted sub-scores (config('nas.ranking_weights')).
     */
    public function score(Na $na, ?Carbon $from = null, ?Carbon $to = null): float
    {
        [$from, $to] = [$from ?? Carbon::today()->subDays(30), $to ?? Carbon::today()];
        $summary = $this->summarize($na, $from, $to);
        $weights = config('nas.ranking_weights');

        $taskCompletionRate = $summary['tasks_assigned'] > 0
            ? ($summary['tasks_completed'] / $summary['tasks_assigned']) * 100
            : 0;

        $reportSubmissionRate = $summary['active_volunteers'] > 0
            ? min(100, ($summary['volunteers_who_reported'] / $summary['active_volunteers']) * 100)
            : 0;

        $attendanceRate = $summary['attendance_rate'];

        // Simple, documented, and isolated here specifically so it's easy to
        // retune later: activity relative to headcount (meetings + tasks per
        // active volunteer), capped at 100.
        $activityLevel = $summary['active_volunteers'] > 0
            ? min(100, (($summary['meetings_conducted'] + $summary['tasks_assigned']) / $summary['active_volunteers']) * 20)
            : 0;

        $score = $taskCompletionRate * $weights['task_completion_rate']
            + $reportSubmissionRate * $weights['report_submission_rate']
            + $attendanceRate * $weights['attendance_rate']
            + $activityLevel * $weights['activity_level'];

        return round($score, 1);
    }

    /**
     * @param  Collection<int, Na>  $nas
     */
    public function compare(Collection $nas, Carbon $from, Carbon $to): array
    {
        $rows = $nas->map(function (Na $na) use ($from, $to) {
            $summary = $this->summarize($na, $from, $to);

            return [
                'na' => $na,
                'summary' => $summary,
                'score' => $this->score($na, $from, $to),
            ];
        })->sortByDesc('score')->values();

        return [
            'rows' => $rows,
            'labels' => $rows->pluck('na.name'),
            'task_completion' => $rows->map(fn ($r) => $r['summary']['tasks_assigned'] > 0
                ? round(($r['summary']['tasks_completed'] / $r['summary']['tasks_assigned']) * 100, 1)
                : 0),
            'attendance' => $rows->map(fn ($r) => $r['summary']['attendance_rate']),
            'report_submission' => $rows->map(fn ($r) => $r['summary']['reports_submitted']),
            'fund_collection' => $rows->map(fn ($r) => $r['summary']['fund_collection']),
            'scores' => $rows->pluck('score'),
        ];
    }

    private function attendanceRate(array $memberIds, Carbon $from, Carbon $to): float
    {
        $query = DB::table('meeting_attendances')
            ->join('scheduled_meetings', 'scheduled_meetings.id', '=', 'meeting_attendances.scheduled_meeting_id')
            ->whereIn('meeting_attendances.user_id', $memberIds)
            ->whereBetween('scheduled_meetings.meeting_date', [$from, $to]);

        $totalMarked = (clone $query)->count();
        if ($totalMarked === 0) {
            return 0;
        }

        $attended = (clone $query)->whereIn('meeting_attendances.status', ['present', 'late'])->count();

        return round(($attended / $totalMarked) * 100, 1);
    }

    /**
     * Completed tasks whose Project is in this NA (via its UC) and sits
     * under a department matching $keyword (Hospital, Mosque, Khidmat,
     * Events, ...) — department names are free text set by the org itself,
     * so a loose match against the org's own naming convention is
     * intentional here. Project carries uc_id directly since Department is
     * shared/global across every NA/UC.
     */
    private function activityCount(Na $na, string $keyword, Carbon $from, Carbon $to): int
    {
        return Task::whereIn('status', ['completed', 'approved', 'closed'])
            ->whereBetween('updated_at', [$from, $to])
            ->whereHas('project', fn ($q) => $q->whereHas('uc', fn ($q2) => $q2->where('na_id', $na->id))
                ->whereHas('department', fn ($q2) => $q2->where('name', 'like', "%{$keyword}%")))
            ->count();
    }
}
