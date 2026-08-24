<?php

namespace App\Http\Controllers\Api;

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

        return response()->json([
            'summary' => $evaluator->summarize($user, $from, $to),
            'trend' => $evaluator->monthlyTrend($user),
        ]);
    }
}
