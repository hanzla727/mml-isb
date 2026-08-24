<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HierarchyScope;
use App\Services\PerformanceEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        $query = User::role('user')->where('is_active', true);
        HierarchyScope::restrictUsersQuery($query, $request->user());

        $monthStart = Carbon::today()->startOfMonth();

        $volunteers = $query->withSum(['dailyReports as month_hours' => fn ($q) => $q->whereBetween('report_date', [$monthStart, Carbon::today()])], 'total_hours')
            ->orderBy('name')
            ->get();

        return view('admin.performance.index', ['volunteers' => $volunteers]);
    }

    public function show(Request $request, User $user, PerformanceEvaluator $evaluator)
    {
        $this->authorizeAccess($request);
        abort_unless(HierarchyScope::canView($request->user(), $user), 403);

        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : Carbon::today()->subDays(30);
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : Carbon::today();

        return view('admin.performance.show', [
            'volunteer' => $user,
            'summary' => $evaluator->summarize($user, $from, $to),
            'trend' => $evaluator->monthlyTrend($user),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->can('view-analytics') || $request->user()->can('review-reports'), 403);
    }
}
