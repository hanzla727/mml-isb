<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Project;
use App\Models\Uc;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()->with(['department', 'uc'])->withCount(['meetings', 'tasks']);
        $this->restrictToVisibleUcs($query, $request);

        $projects = $query->orderByDesc('created_at')->get()
            ->map(function (Project $project) {
                $project->progress_percent = $project->progress();

                return $project;
            });

        return view('admin.projects.index', [
            'projects' => $projects,
            'departments' => Department::orderBy('name')->get(),
            'ucs' => $this->visibleUcs($request),
        ]);
    }

    public function show(Request $request, Project $project)
    {
        abort_unless(
            HierarchyScope::visibleUcIds($request->user()) === null
                || in_array($project->uc_id, HierarchyScope::visibleUcIds($request->user()), true),
            403
        );

        return view('admin.projects.show', [
            'project' => $project->load(['department', 'uc', 'meetings', 'tasks.assignees']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        Project::create([...$validated, 'created_by' => $request->user()->id]);

        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));

        return back()->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', 'Project deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'uc_id' => ['required', 'exists:ucs,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['planning', 'active', 'completed', 'on_hold'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function restrictToVisibleUcs($query, Request $request): void
    {
        $ucIds = HierarchyScope::visibleUcIds($request->user());

        if ($ucIds !== null) {
            $query->whereIn('uc_id', $ucIds);
        }
    }

    private function visibleUcs(Request $request)
    {
        $ucIds = HierarchyScope::visibleUcIds($request->user());

        return Uc::query()
            ->when($ucIds !== null, fn ($q) => $q->whereIn('id', $ucIds))
            ->orderBy('name')
            ->get();
    }
}
