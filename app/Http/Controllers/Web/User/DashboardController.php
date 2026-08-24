<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetrics;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMetrics $metrics)
    {
        return view('user.dashboard', [
            'stats' => $metrics->forUser($request->user()),
            'announcements' => $request->user()
                ->notifications()
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
