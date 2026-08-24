<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitTaskReportRequest;
use App\Models\FormSubmission;
use App\Models\Media;
use App\Models\Task;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::query()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->with(['scheduledMeeting', 'latestReport'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('user.tasks.index', ['tasks' => $tasks]);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return view('user.tasks.show', [
            'task' => $task->load(['scheduledMeeting', 'formTemplate.fields', 'reports' => fn ($q) => $q->where('user_id', auth()->id()), 'comments.user']),
            'myFormSubmission' => $task->form_template_id
                ? $task->formSubmissions()->where('user_id', auth()->id())->with('values')->first()
                : null,
        ]);
    }

    public function submitReport(SubmitTaskReportRequest $request, Task $task, TaskWorkflowService $service)
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

        return redirect()->route('user.tasks.show', $task)->with('status', 'Report submitted.');
    }

    public function submitFormResponse(Request $request, Task $task)
    {
        abort_unless($task->form_template_id, 404);
        $this->authorize('submitReport', $task);

        $formTemplate = $task->formTemplate()->with('fields')->first();

        $rules = [];
        foreach ($formTemplate->fields as $field) {
            $rules["field_{$field->id}"] = [$field->is_required ? 'required' : 'nullable'];
            if (in_array($field->field_type, ['file', 'image'], true)) {
                $rules["field_{$field->id}"][] = 'file';
            }
        }
        $validated = $request->validate($rules);

        $submission = FormSubmission::create([
            'form_template_id' => $formTemplate->id,
            'submittable_type' => Task::class,
            'submittable_id' => $task->id,
            'user_id' => $request->user()->id,
        ]);

        foreach ($formTemplate->fields as $field) {
            $key = "field_{$field->id}";

            if (in_array($field->field_type, ['file', 'image'], true) && $request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store('form-submissions', 'public');
                $media = Media::create([
                    'disk' => 'public',
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);

                $submission->values()->create(['form_field_id' => $field->id, 'media_id' => $media->id]);
            } else {
                $submission->values()->create(['form_field_id' => $field->id, 'value' => $validated[$key] ?? null]);
            }
        }

        return redirect()->route('user.tasks.show', $task)->with('status', 'Form submitted.');
    }
}
