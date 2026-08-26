<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\HierarchyScope;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Task::query()->with(['scheduledMeeting', 'assignees', 'latestReport']);

        if ($user->can('manage-tasks')) {
            HierarchyScope::restrictByRelation($query, $user, 'assignees');

            $query->when($request->filled('department_id'), fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('department_id', $request->integer('department_id'))))
                ->when($request->filled('user_id'), fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $request->integer('user_id'))))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
                ->when($request->filled('meeting_id'), fn ($q) => $q->where('scheduled_meeting_id', $request->integer('meeting_id')));
        } else {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')));
        }

        $tasks = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return TaskResource::collection($tasks);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['scheduledMeeting', 'assignees', 'latestReport', 'comments.user']));
    }

    public function store(StoreTaskRequest $request, TaskWorkflowService $service)
    {
        $this->authorize('create', Task::class);

        $task = $service->create($request->user(), $request->validated());

        return new TaskResource($task->load(['assignees', 'scheduledMeeting']));
    }

    public function update(StoreTaskRequest $request, Task $task, TaskWorkflowService $service)
    {
        $this->authorize('update', $task);

        $validated = $request->validated();

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $service->assign($task, $validated, $request->user());

        return new TaskResource($task->fresh(['assignees', 'scheduledMeeting']));
    }

    public function comments(Request $request, Task $task, TaskWorkflowService $service)
    {
        $this->authorize('view', $task);

        $request->validate(['body' => ['required', 'string']]);

        $comment = $service->addComment($task, $request->user(), $request->string('body')->toString());

        return response()->json(['data' => $comment->load('user')]);
    }
}
