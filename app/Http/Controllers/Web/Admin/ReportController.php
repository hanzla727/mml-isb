<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DailyReportsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewDailyReportRequest;
use App\Models\DailyReport;
use App\Services\HierarchyScope;
use App\Services\ReportApprovalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function show(DailyReport $dailyReport)
    {
        return view('admin.reports.show', [
            'report' => $dailyReport->load(['user', 'meetings.contact', 'meetings.participants', 'teamLeader', 'teamLeaderReviewer', 'adminReviewer']),
        ]);
    }

    public function review(ReviewDailyReportRequest $request, DailyReport $dailyReport, ReportApprovalService $service)
    {
        $decision = $request->string('decision')->toString();
        $remarks = $request->input('remarks');

        if ($dailyReport->review_status === 'pending_review') {
            $this->authorize('reviewAsTeamLeader', $dailyReport);
            $service->teamLeaderReview($dailyReport, $request->user(), $decision, $remarks);
        } else {
            $this->authorize('reviewAsAdmin', $dailyReport);
            $service->adminReview($dailyReport, $request->user(), $decision, $remarks);
        }

        return back()->with('status', 'Report reviewed.');
    }

    public function export(Request $request)
    {
        $reports = $this->filteredQuery($request)->orderByDesc('report_date')->get();
        $format = $request->string('format', 'csv');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.export-pdf', ['reports' => $reports]);

            return $pdf->download('daily-reports.pdf');
        }

        return Excel::download(new DailyReportsExport($reports), "daily-reports.{$format}");
    }

    private function filteredQuery(Request $request)
    {
        $query = DailyReport::query()
            ->with(['user', 'meetings'])
            ->withCount('meetings');

        HierarchyScope::restrictByOwner($query, $request->user());

        return $query
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('team_id'), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('team_id', $request->integer('team_id'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('review_status'), fn ($q) => $q->where('review_status', $request->string('review_status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('report_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('report_date', '<=', $request->date('to')));
    }
}
