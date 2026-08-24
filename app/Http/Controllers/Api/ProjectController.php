<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Projects the volunteer is actually involved in — a Project has no
     * direct link to a user, only indirectly through the tasks assigned to
     * them or the scheduled meetings they participate in (same rule
     * DashboardMetrics::forUser() uses for its "assigned_projects" tile).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $projects = Project::whereHas('tasks', fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $user->id)))
            ->orWhereHas('meetings', fn ($q) => $q->whereHas('participants', fn ($q2) => $q2->where('users.id', $user->id)))
            ->distinct()
            ->orderBy('name')
            ->get();

        $projects->each(fn (Project $project) => $project->setAttribute(
            'my_tasks_count',
            $project->tasks()->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))->count()
        ));

        return ProjectResource::collection($projects);
    }

    /**
     * Detail view — adds who else is working on it, grouped by their org
     * Team (derived from task assignees + meeting participants, since
     * Project itself carries no Team relation).
     */
    public function show(Request $request, Project $project)
    {
        $project->load(['department', 'uc']);

        $taskAssignees = collect();
        foreach ($project->tasks()->with('assignees.team')->get() as $task) {
            $taskAssignees = $taskAssignees->merge($task->assignees);
        }

        $meetingParticipants = collect();
        foreach ($project->meetings()->with('participants.team')->get() as $meeting) {
            $meetingParticipants = $meetingParticipants->merge($meeting->participants);
        }

        $teams = $taskAssignees->merge($meetingParticipants)
            ->unique('id')
            ->groupBy(fn ($user) => $user->team?->name ?? 'Unassigned')
            ->map(fn ($users, $teamName) => [
                'team' => $teamName,
                'members' => $users->map(fn ($user) => ['id' => $user->id, 'name' => $user->name])->values(),
            ])
            ->values();

        $project->setAttribute('team_members', $teams);
        $project->setAttribute(
            'my_tasks_count',
            $project->tasks()->whereHas('assignees', fn ($q) => $q->where('users.id', $request->user()->id))->count()
        );

        return new ProjectResource($project);
    }
}
