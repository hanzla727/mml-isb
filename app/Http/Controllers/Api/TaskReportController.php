<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewTaskReportRequest;
use App\Http\Requests\SubmitTaskReportRequest;
use App\Http\Resources\TaskReportResource;
use App\Models\Media;
use App\Models\Task;
use App\Models\TaskReport;
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
}
