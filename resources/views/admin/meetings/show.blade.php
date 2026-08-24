@extends('layouts.admin')

@section('title', $meeting->title)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $meeting->title }}</h5>
            <span class="badge bg-secondary">{{ ucfirst($meeting->status) }}</span>
        </div>
        <p class="text-muted small mb-2">
            {{ $meeting->meeting_date->toDateString() }}, {{ $meeting->start_time }}&ndash;{{ $meeting->end_time }}
            @if ($meeting->location) &middot; {{ $meeting->location }} @endif
        </p>
        @if ($meeting->project)
            <div class="mb-2"><strong>Project:</strong> <a href="{{ route('admin.projects.show', $meeting->project) }}">{{ $meeting->project->name }}</a></div>
        @endif
        <div class="mb-2"><strong>Organizer:</strong> {{ $meeting->organizer->name }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $meeting->description ?: '—' }}</div>
        <div class="mb-2"><strong>Agenda:</strong> {{ $meeting->agenda ?: '—' }}</div>
        <div class="mb-2">
            <strong>Participants ({{ $meeting->participants->count() }}):</strong>
            {{ $meeting->participants->pluck('name')->join(', ') ?: '—' }}
        </div>

        @can('manage-meetings')
            <form method="POST" action="{{ route('admin.meetings.destroy', $meeting) }}" onsubmit="return confirm('Cancel this meeting?')" class="mt-2">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Cancel Meeting</button>
            </form>
        @endcan
    </div>

    <div class="card stat-card p-4 mb-3">
        <h6 class="mb-3">Attendance</h6>

        @if ($meeting->participants->isEmpty())
            <p class="text-muted small mb-0">No participants to mark attendance for.</p>
        @else
            @can('update', $meeting)
                <form method="POST" action="{{ route('admin.meetings.attendance', $meeting) }}">
                    @csrf @method('PUT')
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>Participant</th><th style="width: 200px;">Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($meeting->participants as $participant)
                                @php $existing = $meeting->attendances->firstWhere('user_id', $participant->id); @endphp
                                <tr>
                                    <td>{{ $participant->name }}</td>
                                    <td>
                                        <select name="attendance[{{ $participant->id }}]" class="form-select form-select-sm">
                                            @foreach (['absent', 'present', 'late', 'excused'] as $status)
                                                <option value="{{ $status }}" @selected(($existing?->status ?? 'absent') === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-sm btn-primary">Save Attendance</button>
                </form>
            @else
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr><th>Participant</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($meeting->participants as $participant)
                            @php $existing = $meeting->attendances->firstWhere('user_id', $participant->id); @endphp
                            <tr>
                                <td>{{ $participant->name }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($existing?->status ?? 'absent') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endcan
        @endif
    </div>

    <div class="card stat-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Tasks ({{ $meeting->tasks->count() }})</h6>
            @can('manage-tasks')
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ Add Task</button>
            @endcan
        </div>

        @forelse ($meeting->tasks as $task)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.tasks.show', $task) }}"><strong>{{ $task->title }}</strong></a>
                    <div>
                        <span class="badge bg-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($task->priority) }}</span>
                        <span class="badge bg-info text-dark">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
                        @if ($task->isOverdue())
                            <span class="badge bg-danger">Overdue</span>
                        @endif
                    </div>
                </div>
                <div class="text-muted small">Due: {{ $task->due_date?->toDateString() ?? '—' }}</div>
                <div class="text-muted small">Assigned to: {{ $task->assignees->pluck('name')->join(', ') ?: '—' }}</div>
            </div>
        @empty
            <p class="text-muted small mb-0">No tasks added to this meeting yet.</p>
        @endforelse
    </div>

    @can('manage-tasks')
        <div class="modal fade" id="addTaskModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.tasks.store') }}">
                        @csrf
                        <input type="hidden" name="scheduled_meeting_id" value="{{ $meeting->id }}">
                        @if ($meeting->project_id)
                            <input type="hidden" name="project_id" value="{{ $meeting->project_id }}">
                        @endif
                        <div class="modal-header"><h5 class="modal-title">Add Task</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
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
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="taskIsRecurring">
                                <label class="form-check-label" for="taskIsRecurring">Repeats</label>
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

                            @include('admin.partials.audience-picker', compact('nas', 'ucs', 'departments', 'teams', 'users'))
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
