<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Models\Task;
use App\Services\TaskWorkflowService;
use Spatie\Activitylog\Models\Activity;

class TaskController extends Controller
{
    public function index()
    {
        return view('admin.tasks.index');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return view('admin.tasks.show', [
            'task' => $task->load(['assignees', 'reports.user', 'reports.attachments', 'comments.user', 'scheduledMeeting']),
            'timeline' => Activity::where('subject_type', Task::class)->where('subject_id', $task->id)->with('causer')->orderBy('created_at')->get(),
        ]);
    }

    public function store(StoreTaskRequest $request, TaskWorkflowService $service)
    {
        $this->authorize('create', Task::class);

        $task = $service->create($request->user(), $request->validated());

        return back()->with('status', 'Task added.')->with('task_id', $task->id);
    }

    public function update(StoreTaskRequest $request, Task $task, TaskWorkflowService $service)
    {
        $this->authorize('update', $task);

        $validated = $request->validated();

        $task->update([
            'project_id' => $validated['project_id'] ?? $task->project_id,
            'form_template_id' => $validated['form_template_id'] ?? $task->form_template_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $service->assign($task, $validated);

        return back()->with('status', 'Task updated.');
    }
}
