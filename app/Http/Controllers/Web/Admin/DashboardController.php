<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Na;
use App\Services\DashboardMetrics;
use App\Services\HierarchyScope;
use App\Services\NaPerformanceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMetrics $metrics, NaPerformanceService $performance)
    {
        $query = Na::query();
        HierarchyScope::restrictByNa($query, $request->user(), 'id');

        $topNas = $query->get()
            ->map(fn (Na $na) => ['na' => $na, 'score' => $performance->score($na)])
            ->sortByDesc('score')
            ->take(3)
            ->values();

        return view('admin.dashboard', [
            'stats' => $metrics->forAdmin($request->user()),
            'topNas' => $topNas,
        ]);
    }
}
