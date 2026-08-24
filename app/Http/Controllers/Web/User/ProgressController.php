<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::today()->subMonths($i)->startOfMonth());

        $monthlyHours = $months->map(function (Carbon $month) use ($user) {
            return (float) $user->dailyReports()
                ->whereBetween('report_date', [$month, $month->copy()->endOfMonth()])
                ->sum('total_hours');
        });

        $monthlyMeetings = $months->map(function (Carbon $month) use ($user) {
            return Meeting::whereHas('dailyReport', fn ($q) => $q->where('user_id', $user->id)
                ->whereBetween('report_date', [$month, $month->copy()->endOfMonth()])
            )->count();
        });

        return view('user.progress.index', [
            'labels' => $months->map(fn ($m) => $m->format('M Y')),
            'monthlyHours' => $monthlyHours,
            'monthlyMeetings' => $monthlyMeetings,
        ]);
    }
}
