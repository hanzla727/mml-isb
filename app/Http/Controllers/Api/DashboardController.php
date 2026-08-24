<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetrics;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMetrics $metrics)
    {
        $user = $request->user();

        $isReviewer = $user->can('view-analytics') || $user->can('review-reports');

        return response()->json(
            $isReviewer ? $metrics->forAdmin($user) : $metrics->forUser($user)
        );
    }
}
