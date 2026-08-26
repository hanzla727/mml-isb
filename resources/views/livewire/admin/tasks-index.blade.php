<div>
    <div class="card stat-card filter-bar p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-muted">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Task title...">
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">Status</label>
                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['not_assigned', 'assigned', 'accepted', 'in_progress', 'waiting_for_information', 'completed', 'report_submitted', 'under_review', 'approved', 'rejected', 'needs_revision', 're_submitted', 'closed', 'cancelled'] as $option)
                        <option value="{{ $option }}">{{ str_replace('_', ' ', ucfirst($option)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">Priority</label>
                <select wire:model.live="priority" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['low', 'medium', 'high', 'critical'] as $option)
                        <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">Department</label>
                <select wire:model.live="departmentId" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-1">
                <label class="form-label small text-muted">Project</label>
                <select wire:model.live="projectId" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-2 align-items-center mt-1">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-muted">Volunteer</label>
                <select wire:model.live="userId" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3 pt-4">
                <div class="form-check">
                    <input type="checkbox" wire:model.live="overdueOnly" class="form-check-input" id="overdueOnly">
                    <label class="form-check-label small" for="overdueOnly">Overdue only</label>
                </div>
            </div>
            <div class="col-md-4 col-lg-6 d-flex justify-content-end gap-2 pt-3">
                @if ($this->hasActiveFilters)
                    <button type="button" wire:click="resetFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear filters
                    </button>
                @endif
                @can('manage-tasks')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="bi bi-plus-lg"></i> Add Task
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="card stat-card" wire:loading.class="is-loading-target" wire:target="search,status,priority,departmentId,userId,projectId,overdueOnly,resetFilters">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Project / Meeting</th>
                    <th>Priority</th>
                    <th>Due</th>
                    <th>Assignees</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr wire:key="task-{{ $task->id }}">
                        <td>{{ $task->title }}</td>
                        <td class="text-muted small">{{ $task->project?->name ?? $task->scheduledMeeting?->title ?? '—' }}</td>
                        <td><x-status-badge :status="$task->priority" /></td>
                        <td>
                            {{ $task->due_date?->toDateString() ?? '—' }}
                            @if ($task->isOverdue())<span class="badge bg-danger">Overdue</span>@endif
                        </td>
                        <td>{{ $task->assignees->pluck('name')->join(', ') ?: '—' }}</td>
                        <td><x-status-badge :status="$task->status" /></td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @can('manage-tasks')
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}"
                                    data-description="{{ $task->description }}"
                                    data-project-id="{{ $task->project_id }}"
                                    data-priority="{{ $task->priority }}"
                                    data-due-date="{{ $task->due_date?->toDateString() }}"
                                    data-due-time="{{ $task->due_time }}"
                                    data-notes="{{ $task->notes }}"
                                    data-form-template-id="{{ $task->form_template_id }}"
                                    data-assignee-ids="{{ $task->assignees->pluck('id')->implode(',') }}"
                                    onclick="openEditTaskModal(this.dataset)">
                                    Edit
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-list-check"></i>
                                No tasks match these filters.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tasks->links() }}</div>

    @can('manage-tasks')
        <div class="modal fade" id="createTaskModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.tasks.store') }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">New Task</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Project (optional)</label>
                                <select name="project_id" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Due Time</label>
                                    <input type="time" name="due_time" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attached Form (optional)</label>
                                <select name="form_template_id" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($formTemplates as $formTemplate)
                                        <option value="{{ $formTemplate->id }}">{{ $formTemplate->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="createTaskIsRecurring">
                                <label class="form-check-label" for="createTaskIsRecurring">Repeats</label>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Frequency</label>
                                    <select name="recurrence_frequency" class="form-select form-select-sm">
                                        <option value="weekly">Weekly</option>
                                        <option value="daily">Daily</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Every</label>
                                    <input type="number" name="recurrence_interval" value="1" min="1" max="52" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Until</label>
                                    <input type="date" name="recurrence_until" class="form-control form-control-sm">
                                </div>
                            </div>

                            @include('admin.partials.audience-picker', compact('nas', 'ucs', 'departments', 'users'))
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Create Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editTaskModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editTaskForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">Edit Task</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="editTaskTitle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="editTaskDescription" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Project (optional)</label>
                                <select name="project_id" id="editTaskProject" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" id="editTaskPriority" class="form-select">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="due_date" id="editTaskDueDate" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Due Time</label>
                                    <input type="time" name="due_time" id="editTaskDueTime" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" id="editTaskNotes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attached Form (optional)</label>
                                <select name="form_template_id" id="editTaskFormTemplate" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($formTemplates as $formTemplate)
                                        <option value="{{ $formTemplate->id }}">{{ $formTemplate->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="editTaskIsRecurring">
                                <label class="form-check-label" for="editTaskIsRecurring">Repeats</label>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Frequency</label>
                                    <select name="recurrence_frequency" class="form-select form-select-sm">
                                        <option value="weekly">Weekly</option>
                                        <option value="daily">Daily</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Every</label>
                                    <input type="number" name="recurrence_interval" value="1" min="1" max="52" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Until</label>
                                    <input type="date" name="recurrence_until" class="form-control form-control-sm">
                                </div>
                            </div>

                            <p class="text-muted small">Re-select who this task is for below — the assignees are re-applied on save.</p>
                            @include('admin.partials.audience-picker', compact('nas', 'ucs', 'departments', 'users'))
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const taskUpdateUrlTemplate = @json(route('admin.tasks.update', ['task' => '__ID__']));

            function openEditTaskModal(data) {
                const form = document.getElementById('editTaskForm');
                form.reset();
                form.action = taskUpdateUrlTemplate.replace('__ID__', data.id);

                document.getElementById('editTaskTitle').value = data.title ?? '';
                document.getElementById('editTaskDescription').value = data.description ?? '';
                document.getElementById('editTaskProject').value = data.projectId ?? '';
                document.getElementById('editTaskPriority').value = data.priority ?? 'medium';
                document.getElementById('editTaskDueDate').value = data.dueDate ?? '';
                document.getElementById('editTaskDueTime').value = data.dueTime ? data.dueTime.substring(0, 5) : '';
                document.getElementById('editTaskNotes').value = data.notes ?? '';
                document.getElementById('editTaskFormTemplate').value = data.formTemplateId ?? '';

                const scopeSelect = form.querySelector('.audience-scope-select');
                scopeSelect.value = 'individual';
                scopeSelect.dispatchEvent(new Event('change'));

                const assigneeIds = (data.assigneeIds ?? '').split(',').filter(Boolean);
                form.querySelectorAll('.audience-user-option input[type="checkbox"]').forEach((checkbox) => {
                    checkbox.checked = assigneeIds.includes(checkbox.value);
                });

                new bootstrap.Modal(document.getElementById('editTaskModal')).show();
            }
        </script>
    @endcan
</div>
