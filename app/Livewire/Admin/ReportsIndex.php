<?php

namespace App\Livewire\Admin;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\User;
use App\Services\HierarchyScope;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReportsIndex extends Component
{
    use WithPagination;

    public string $paginationTheme = 'bootstrap';

    #[Url(history: true)]
    public string $userId = '';

    #[Url(history: true)]
    public string $departmentId = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $reviewStatus = '';

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
        $this->reset(['userId', 'departmentId', 'status', 'reviewStatus', 'from', 'to']);
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->userId !== '' || $this->departmentId !== ''
            || $this->status !== '' || $this->reviewStatus !== '' || $this->from !== '' || $this->to !== '';
    }

    public function getExportParamsProperty(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'department_id' => $this->departmentId,
            'status' => $this->status,
            'review_status' => $this->reviewStatus,
            'from' => $this->from,
            'to' => $this->to,
        ], fn ($value) => $value !== '');
    }

    public function render()
    {
        $viewer = auth()->user();
        $visibleIds = HierarchyScope::visibleUserIds($viewer);

        $query = DailyReport::query()->with(['user.department'])->withCount('meetings');
        HierarchyScope::restrictByOwner($query, $viewer);

        $query
            ->when($this->userId !== '', fn ($q) => $q->where('user_id', $this->userId))
            ->when($this->departmentId !== '', fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('department_id', $this->departmentId)))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->reviewStatus !== '', fn ($q) => $q->where('review_status', $this->reviewStatus))
            ->when($this->from !== '', fn ($q) => $q->whereDate('report_date', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('report_date', '<=', $this->to));

        $reports = $query->orderByDesc('report_date')->paginate(15);

        $users = User::role('user')
            ->when($visibleIds !== null, fn ($q) => $q->whereIn('id', $visibleIds))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.reports-index', [
            'reports' => $reports,
            'users' => $users,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
