<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Admin/NA Head/UC Head see every project across their scope (every
     * project whose uc_id falls under the UCs HierarchyScope grants them),
     * so an NA Head sees all of their NA's projects, not just the ones
     * they personally happen to have a task or meeting on. Everyone else
     * (Team Leader, plain volunteer) still only sees projects they're
     * actually involved in — a Project has no direct link to a user, only
     * indirectly through the tasks assigned to them or the scheduled
     * meetings they participate in (same rule DashboardMetrics::forUser()
     * uses for its "assigned_projects" tile).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasAnyRole(['super_admin', 'admin', 'na_head', 'uc_head'])) {
            $query = Project::query();
            HierarchyScope::restrictByUc($query, $user);
            $projects = $query->orderBy('name')->get();
        } else {
            $projects = Project::whereHas('tasks', fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $user->id)))
                ->orWhereHas('meetings', fn ($q) => $q->whereHas('participants', fn ($q2) => $q2->where('users.id', $user->id)))
                ->distinct()
                ->orderBy('name')
                ->get();
        }

        $projects->each(fn (Project $project) => $project->setAttribute(
            'my_tasks_count',
            $project->tasks()->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))->count()
        ));

        return ProjectResource::collection($projects);
    }

    /**
     * Detail view — adds who else is working on it, grouped by their own
     * Department (derived from task assignees + meeting participants,
     * since Project itself carries no direct relation to a user).
     */
    public function show(Request $request, Project $project)
    {
        $project->load(['department', 'uc']);

        $taskAssignees = collect();
        foreach ($project->tasks()->with('assignees.department')->get() as $task) {
            $taskAssignees = $taskAssignees->merge($task->assignees);
        }

        $meetingParticipants = collect();
        foreach ($project->meetings()->with('participants.department')->get() as $meeting) {
            $meetingParticipants = $meetingParticipants->merge($meeting->participants);
        }

        $departments = $taskAssignees->merge($meetingParticipants)
            ->unique('id')
            ->groupBy(fn ($user) => $user->department?->name ?? 'Unassigned')
            ->map(fn ($users, $departmentName) => [
                'department' => $departmentName,
                'members' => $users->map(fn ($user) => ['id' => $user->id, 'name' => $user->name])->values(),
            ])
            ->values();

        $project->setAttribute('department_members', $departments);
        $project->setAttribute(
            'my_tasks_count',
            $project->tasks()->whereHas('assignees', fn ($q) => $q->where('users.id', $request->user()->id))->count()
        );

        return new ProjectResource($project);
    }
}
