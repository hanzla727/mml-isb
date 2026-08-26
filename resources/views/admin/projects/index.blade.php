@extends('layouts.admin')

@section('title', __('Projects'))

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">{{ __('Add Project') }}</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Department / UC') }}</th><th>{{ __('Status') }}</th><th>{{ __('Dates') }}</th><th>{{ __('Meetings') }}</th><th>{{ __('Tasks') }}</th><th>{{ __('Progress') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td><a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a></td>
                        <td class="text-muted small">{{ $project->department->name }} / {{ $project->uc->name }}</td>
                        <td><x-status-badge :status="$project->status" /></td>
                        <td class="text-muted small">
                            {{ $project->start_date?->toDateString() ?? '—' }} &ndash; {{ $project->end_date?->toDateString() ?? '—' }}
                        </td>
                        <td>{{ $project->meetings_count }}</td>
                        <td>{{ $project->tasks_count }}</td>
                        <td style="width: 150px;">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ $project->progress_percent }}%"></div>
                            </div>
                            <small class="text-muted">{{ $project->progress_percent }}%</small>
                        </td>
                        <td class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $project->id }}">{{ __('Edit') }}</button>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('{{ __('Delete this project?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $project->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.projects.update', $project) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">{{ __('Edit Project') }}</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Department') }}</label>
                                            <select name="department_id" class="form-select" required>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" @selected($project->department_id === $department->id)>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('UC') }}</label>
                                            <select name="uc_id" class="form-select" required>
                                                @foreach ($ucs as $uc)
                                                    <option value="{{ $uc->id }}" @selected($project->uc_id === $uc->id)>{{ $uc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Name') }}</label>
                                            <input type="text" name="name" value="{{ $project->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Description') }}</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $project->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Status') }}</label>
                                            <select name="status" class="form-select" required>
                                                @foreach (['planning', 'active', 'completed', 'on_hold'] as $status)
                                                    <option value="{{ $status }}" @selected($project->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Start Date') }}</label>
                                                <input type="date" name="start_date" value="{{ $project->start_date?->toDateString() }}" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('End Date') }}</label>
                                                <input type="date" name="end_date" value="{{ $project->end_date?->toDateString() }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">{{ __('No projects yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.projects.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">{{ __('Add Project') }}</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Department') }}</label>
                            <select name="department_id" class="form-select" required>
                                <option value="" selected disabled>{{ __('Choose a department…') }}</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('UC') }}</label>
                            <select name="uc_id" class="form-select" required>
                                <option value="" selected disabled>{{ __('Choose a UC…') }}</option>
                                @foreach ($ucs as $uc)
                                    <option value="{{ $uc->id }}">{{ $uc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select" required>
                                <option value="planning" selected>{{ __('Planning') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                                <option value="on_hold">{{ __('On Hold') }}</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('End Date') }}</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
