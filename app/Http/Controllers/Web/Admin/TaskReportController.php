<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewTaskReportRequest;
use App\Models\TaskReport;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = TaskReport::query()
            ->with(['task', 'user'])
            ->when($request->filled('status'), fn ($q) => $q->where('review_status', $request->string('status')), fn ($q) => $q->whereIn('review_status', ['pending', 'under_review', 're_submitted']))
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('admin.task-reports.index', ['reports' => $reports]);
    }

    public function show(TaskReport $taskReport)
    {
        $this->authorize('review', $taskReport);

        return view('admin.task-reports.show', [
            'report' => $taskReport->load(['task.assignees', 'user', 'attachments', 'reviewer']),
            'previousVersions' => $taskReport->task->reports()->where('user_id', $taskReport->user_id)->where('id', '!=', $taskReport->id)->get(),
        ]);
    }

    public function review(ReviewTaskReportRequest $request, TaskReport $taskReport, TaskWorkflowService $service)
    {
        $this->authorize('review', $taskReport);

        $service->review($taskReport, $request->user(), $request->string('decision')->toString(), $request->input('remarks'));

        return redirect()->route('admin.task-reports.index')->with('status', 'Report reviewed.');
    }
}
