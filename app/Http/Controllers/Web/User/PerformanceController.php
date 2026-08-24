<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Services\PerformanceEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PerformanceController extends Controller
{
    public function index(Request $request, PerformanceEvaluator $evaluator)
    {
        $user = $request->user();
        $from = Carbon::today()->subDays(30);
        $to = Carbon::today();

        return view('user.performance.index', [
            'summary' => $evaluator->summarize($user, $from, $to),
            'trend' => $evaluator->monthlyTrend($user),
        ]);
    }
}
