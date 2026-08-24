<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewTaskReportRequest;
use App\Http\Requests\SubmitTaskReportRequest;
use App\Http\Resources\TaskReportResource;
use App\Models\Media;
use App\Models\Task;
use App\Models\TaskReport;
use App\Services\HierarchyScope;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskReportController extends Controller
{
    public function store(SubmitTaskReportRequest $request, Task $task, TaskWorkflowService $service)
    {
        $this->authorize('submitReport', $task);

        $report = $service->submitReport($task, $request->user(), $request->validated());

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('task-reports', 'public');

            $report->attachments()->save(new Media([
                'disk' => 'public',
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]));
        }

        return new TaskReportResource($report->load(['user', 'attachments']));
    }

    public function review(ReviewTaskReportRequest $request, TaskReport $taskReport, TaskWorkflowService $service)
    {
        $this->authorize('review', $taskReport);

        $report = $service->review(
            $taskReport,
            $request->user(),
            $request->string('decision')->toString(),
            $request->input('remarks')
        );

        return new TaskReportResource($report->load(['user', 'reviewer', 'attachments']));
    }

    public function index(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        return TaskReportResource::collection($task->reports()->with(['user', 'attachments'])->get());
    }

    /**
     * The review queue for Team Leaders/NA Heads/Admins — every task report
     * awaiting a decision, scoped via App\Services\HierarchyScope. Distinct
     * from index() above, which lists one specific task's own reports.
     */
    public function pending(Request $request)
    {
        $query = TaskReport::query()->with(['task', 'user']);
        HierarchyScope::restrictByOwner($query, $request->user());

        $reports = $query
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('review_status', $request->string('status')),
                fn ($q) => $q->whereIn('review_status', ['pending', 'under_review', 're_submitted'])
            )
            ->orderByDesc('submitted_at')
            ->paginate($request->integer('per_page', 20));

        return TaskReportResource::collection($reports);
    }
}
