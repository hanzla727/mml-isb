<?php

namespace App\Livewire\Admin;

use App\Models\Na;
use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Project;
use App\Models\ScheduledMeeting;
use App\Models\Team;
use App\Models\Uc;
use App\Models\User;
use App\Services\HierarchyScope;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingsIndex extends Component
{
    use WithPagination;

    public string $paginationTheme = 'bootstrap';

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $projectId = '';

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'projectId', 'from', 'to']);
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' || $this->status !== '' || $this->projectId !== ''
            || $this->from !== '' || $this->to !== '';
    }

    public function render()
    {
        $viewer = auth()->user();

        $query = ScheduledMeeting::query()->with(['organizer', 'project', 'participants:id'])->withCount(['participants', 'tasks']);
        HierarchyScope::restrictByRelation($query, $viewer, 'participants');

        $query
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->projectId !== '', fn ($q) => $q->where('project_id', $this->projectId))
            ->when($this->from !== '', fn ($q) => $q->whereDate('meeting_date', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('meeting_date', '<=', $this->to));

        $meetings = $query->orderBy('meeting_date')->orderBy('start_time')->paginate(15);

        $naIds = HierarchyScope::visibleNaIds($viewer);
        $ucIds = HierarchyScope::visibleUcIds($viewer);

        return view('livewire.admin.meetings-index', [
            'meetings' => $meetings,
            'nas' => Na::when($naIds !== null, fn ($q) => $q->whereIn('id', $naIds))->orderBy('name')->get(),
            'ucs' => Uc::when($ucIds !== null, fn ($q) => $q->whereIn('id', $ucIds))->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'teams' => Team::orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'formTemplates' => FormTemplate::orderBy('name')->get(),
        ]);
    }
}
