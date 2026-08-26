@extends('layouts.admin')

@section('title', $meeting->title)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <h5>{{ $meeting->title }}</h5>
            @can('manage-meetings')
                <form method="POST" action="{{ route('admin.meetings.status', $meeting) }}" class="d-flex gap-2 align-items-center">
                    @csrf @method('PUT')
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                        @foreach (['upcoming', 'ongoing', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($meeting->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <span class="badge bg-secondary">{{ ucfirst($meeting->status) }}</span>
            @endcan
        </div>
        <p class="text-muted small mb-2">
            {{ $meeting->meeting_date->toDateString() }}, {{ $meeting->start_time }}&ndash;{{ $meeting->end_time }}
            @if ($meeting->location) &middot; {{ $meeting->location }} @endif
        </p>
        @if ($meeting->project)
            <div class="mb-2"><strong>{{ __('Project') }}:</strong> <a href="{{ route('admin.projects.show', $meeting->project) }}">{{ $meeting->project->name }}</a></div>
        @endif
        <div class="mb-2"><strong>{{ __('Organizer') }}:</strong> {{ $meeting->organizer->name }}</div>
        <div class="mb-2"><strong>{{ __('Description') }}:</strong> {{ $meeting->description ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Agenda') }}:</strong> {{ $meeting->agenda ?: '—' }}</div>
        <div class="mb-2">
            <strong>{{ __('Participants') }} ({{ $meeting->participants->count() }}):</strong>
            {{ $meeting->participants->pluck('name')->join(', ') ?: '—' }}
        </div>

        @can('manage-meetings')
            <form method="POST" action="{{ route('admin.meetings.destroy', $meeting) }}" onsubmit="return confirm('{{ __('Cancel this meeting?') }}')" class="mt-2">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">{{ __('Cancel Meeting') }}</button>
            </form>
        @endcan
    </div>

    <div class="card stat-card p-4 mb-3">
        <h6 class="mb-3">{{ __('Attendance') }}</h6>

        @if ($meeting->participants->isEmpty())
            <p class="text-muted small mb-0">{{ __('No participants to mark attendance for.') }}</p>
        @else
            @can('update', $meeting)
                <form method="POST" action="{{ route('admin.meetings.attendance', $meeting) }}">
                    @csrf @method('PUT')
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>{{ __('Participant') }}</th><th style="width: 200px;">{{ __('Status') }}</th></tr>
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
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('Save Attendance') }}</button>
                </form>
            @else
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr><th>{{ __('Participant') }}</th><th>{{ __('Status') }}</th></tr>
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
            <h6 class="mb-0">{{ __('Tasks') }} ({{ $meeting->tasks->count() }})</h6>
            @can('manage-tasks')
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ {{ __('Add Task') }}</button>
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
                            <span class="badge bg-danger">{{ __('Overdue') }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-muted small">{{ __('Due') }}: {{ $task->due_date?->toDateString() ?? '—' }}</div>
                <div class="text-muted small">{{ __('Assigned To') }}: {{ $task->assignees->pluck('name')->join(', ') ?: '—' }}</div>
            </div>
        @empty
            <p class="text-muted small mb-0">{{ __('No tasks added to this meeting yet.') }}</p>
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
                        <div class="modal-header"><h5 class="modal-title">{{ __('Add Task') }}</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Priority') }}</label>
                                    <select name="priority" class="form-select">
                                        <option value="low">{{ __('Low') }}</option>
                                        <option value="medium" selected>{{ __('Medium') }}</option>
                                        <option value="high">{{ __('High') }}</option>
                                        <option value="critical">{{ __('Critical') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Due Date') }}</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Due Time') }}</label>
                                    <input type="time" name="due_time" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Attached Form (optional)') }}</label>
                                <select name="form_template_id" class="form-select">
                                    <option value="">— {{ __('None') }} —</option>
                                    @foreach ($formTemplates as $formTemplate)
                                        <option value="{{ $formTemplate->id }}">{{ $formTemplate->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="taskIsRecurring">
                                <label class="form-check-label" for="taskIsRecurring">{{ __('Repeats') }}</label>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('Frequency') }}</label>
                                    <select name="recurrence_frequency" class="form-select form-select-sm">
                                        <option value="weekly">{{ __('Weekly') }}</option>
                                        <option value="daily">{{ __('Daily') }}</option>
                                        <option value="monthly">{{ __('Monthly') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('Every') }}</label>
                                    <input type="number" name="recurrence_interval" value="1" min="1" max="52" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">{{ __('Until') }}</label>
                                    <input type="date" name="recurrence_until" class="form-control form-control-sm">
                                </div>
                            </div>

                            @include('admin.partials.audience-picker', compact('nas', 'ucs', 'departments', 'teams', 'users'))
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">{{ __('Add Task') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
