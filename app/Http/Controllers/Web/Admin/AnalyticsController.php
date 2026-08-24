<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Meeting;
use App\Models\Na;
use App\Services\HierarchyScope;
use App\Services\NaPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request, NaPerformanceService $performance)
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::today()->subMonths($i)->startOfMonth());
        $visibleIds = HierarchyScope::visibleUserIds($request->user());

        $monthlyHours = $months->map(function (Carbon $month) use ($visibleIds) {
            return (float) DailyReport::whereBetween('report_date', [$month, $month->copy()->endOfMonth()])
                ->when($visibleIds !== null, fn ($q) => $q->whereIn('user_id', $visibleIds))
                ->sum('total_hours');
        });

        $monthlyMeetings = $months->map(function (Carbon $month) use ($visibleIds) {
            return Meeting::whereHas('dailyReport', fn ($q) => $q->whereBetween('report_date', [$month, $month->copy()->endOfMonth()])
                ->when($visibleIds !== null, fn ($q2) => $q2->whereIn('user_id', $visibleIds))
            )->count();
        });

        $query = Na::query();
        HierarchyScope::restrictByNa($query, $request->user(), 'id');
        $naRanking = $query->get()
            ->map(fn (Na $na) => ['na' => $na, 'score' => $performance->score($na)])
            ->sortByDesc('score')
            ->values();

        return view('admin.analytics.index', [
            'labels' => $months->map(fn ($m) => $m->format('M Y')),
            'monthlyHours' => $monthlyHours,
            'monthlyMeetings' => $monthlyMeetings,
            'naRanking' => $naRanking,
        ]);
    }
}
