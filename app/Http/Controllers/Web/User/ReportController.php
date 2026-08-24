<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDailyReportRequest;
use App\Models\DailyReport;
use App\Models\Target;
use App\Models\User;
use App\Services\DailyReportManager;
use App\Services\TargetProgressUpdater;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = $request->user()
            ->dailyReports()
            ->withCount('meetings')
            ->orderByDesc('report_date')
            ->paginate(15);

        return view('user.reports.index', ['reports' => $reports]);
    }

    public function create(Request $request, TargetProgressUpdater $progressUpdater)
    {
        return view('user.reports.form', [
            'report' => new DailyReport(['report_date' => now()->toDateString()]),
            'targets' => $this->assignedTargets($request->user(), $progressUpdater),
            'volunteers' => $this->otherActiveVolunteers($request->user()),
        ]);
    }

    public function store(StoreDailyReportRequest $request, DailyReportManager $reports)
    {
        $this->authorize('create', DailyReport::class);

        $report = $reports->create($request->user(), $request->validated());

        return redirect()
            ->route('user.reports.index')
            ->with('status', $report->status === 'draft' ? 'Draft saved.' : 'Report submitted.');
    }

    public function edit(Request $request, DailyReport $dailyReport, TargetProgressUpdater $progressUpdater)
    {
        $this->authorize('update', $dailyReport);

        return view('user.reports.form', [
            'report' => $dailyReport->load(['meetings.contact', 'meetings.participants']),
            'targets' => $this->assignedTargets($request->user(), $progressUpdater),
            'volunteers' => $this->otherActiveVolunteers($request->user()),
        ]);
    }

    public function update(StoreDailyReportRequest $request, DailyReport $dailyReport, DailyReportManager $reports)
    {
        $this->authorize('update', $dailyReport);

        $report = $reports->update($dailyReport, $request->validated());

        return redirect()
            ->route('user.reports.index')
            ->with('status', $report->status === 'draft' ? 'Draft saved.' : 'Report submitted.');
    }

    private function assignedTargets(User $user, TargetProgressUpdater $progressUpdater)
    {
        $targets = Target::query()->where('is_active', true)->applicableTo($user)->get();

        $progressUpdater->attachCurrentProgress($targets, $user);

        return $targets;
    }

    private function otherActiveVolunteers(User $user)
    {
        return User::role('user')->where('is_active', true)->where('id', '!=', $user->id)->orderBy('name')->get();
    }
}
