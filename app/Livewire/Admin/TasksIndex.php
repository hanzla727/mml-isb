<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Na;
use App\Models\Project;
use App\Models\Task;
use App\Models\Uc;
use App\Models\User;
use App\Services\HierarchyScope;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TasksIndex extends Component
{
    use WithPagination;

    public string $paginationTheme = 'bootstrap';

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $priority = '';

    #[Url(history: true)]
    public string $departmentId = '';

    #[Url(history: true)]
    public string $userId = '';

    #[Url(history: true)]
    public string $projectId = '';

    #[Url(history: true)]
    public bool $overdueOnly = false;

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'priority', 'departmentId', 'userId', 'projectId', 'overdueOnly']);
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' || $this->status !== '' || $this->priority !== ''
            || $this->departmentId !== '' || $this->userId !== ''
            || $this->projectId !== '' || $this->overdueOnly;
    }

    public function render()
    {
        $viewer = auth()->user();

        $query = Task::query()->with(['scheduledMeeting', 'project', 'assignees', 'latestReport']);
        HierarchyScope::restrictByRelation($query, $viewer, 'assignees');

        $query
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->priority !== '', fn ($q) => $q->where('priority', $this->priority))
            ->when($this->departmentId !== '', fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('department_id', $this->departmentId)))
            ->when($this->userId !== '', fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $this->userId)))
            ->when($this->projectId !== '', fn ($q) => $q->where('project_id', $this->projectId))
            ->when($this->overdueOnly, fn ($q) => $q->overdue());

        $tasks = $query->orderByDesc('created_at')->paginate(15);

        $naIds = HierarchyScope::visibleNaIds($viewer);
        $ucIds = HierarchyScope::visibleUcIds($viewer);

        return view('livewire.admin.tasks-index', [
            'tasks' => $tasks,
            'nas' => Na::when($naIds !== null, fn ($q) => $q->whereIn('id', $naIds))->orderBy('name')->get(),
            'ucs' => Uc::when($ucIds !== null, fn ($q) => $q->whereIn('id', $ucIds))->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'formTemplates' => FormTemplate::orderBy('name')->get(),
        ]);
    }
}
