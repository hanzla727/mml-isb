<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewDailyReportRequest;
use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Resources\DailyReportResource;
use App\Models\DailyReport;
use App\Services\DailyReportManager;
use App\Services\HierarchyScope;
use App\Services\ReportApprovalService;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', DailyReport::class);

        $user = $request->user();
        $query = DailyReport::query()->with(['user', 'meetings.contact'])->withCount('meetings');

        if ($user->can('view-reports') || $user->can('review-reports')) {
            HierarchyScope::restrictByOwner($query, $user);
            $query->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
                ->when($request->filled('review_status'), fn ($q) => $q->where('review_status', $request->string('review_status')));
        } else {
            $query->where('user_id', $user->id);
        }

        $query->when($request->filled('from'), fn ($q) => $q->whereDate('report_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('report_date', '<=', $request->date('to')));

        $reports = $query->orderByDesc('report_date')->paginate($request->integer('per_page', 15));

        return DailyReportResource::collection($reports);
    }

    /**
     * Always scoped to the authenticated user, regardless of role/permissions
     * (unlike index(), which widens to all reports for admins).
     */
    public function myReports(Request $request)
    {
        $reports = $request->user()
            ->dailyReports()
            ->with(['meetings.contact'])
            ->withCount('meetings')
            ->orderByDesc('report_date')
            ->paginate($request->integer('per_page', 15));

        return DailyReportResource::collection($reports);
    }

    public function store(StoreDailyReportRequest $request, DailyReportManager $reports)
    {
        $this->authorize('create', DailyReport::class);

        $report = $reports->create($request->user(), $request->validated());

        return new DailyReportResource($report);
    }

    public function show(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);

        return new DailyReportResource($dailyReport->load(['meetings.contact', 'user']));
    }

    public function update(StoreDailyReportRequest $request, DailyReport $dailyReport, DailyReportManager $reports)
    {
        $this->authorize('update', $dailyReport);

        $report = $reports->update($dailyReport, $request->validated());

        return new DailyReportResource($report);
    }

    public function review(ReviewDailyReportRequest $request, DailyReport $dailyReport, ReportApprovalService $service)
    {
        $this->authorize('review', $dailyReport);

        $report = $service->review(
            $dailyReport,
            $request->user(),
            $request->string('decision')->toString(),
            $request->input('remarks'),
        );

        return new DailyReportResource($report->load(['user', 'adminReviewer']));
    }
}
