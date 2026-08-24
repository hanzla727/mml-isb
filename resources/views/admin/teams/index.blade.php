@extends('layouts.admin')

@section('title', 'Teams')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add Team</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Department</th><th>UC</th><th>Users</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($teams as $team)
                    <tr>
                        <td>{{ $team->name }}</td>
                        <td>{{ $team->department->name }}</td>
                        <td class="text-muted small">{{ $team->uc->name }}</td>
                        <td>{{ $team->users_count }}</td>
                        <td>
                            <span class="badge {{ $team->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $team->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $team->id }}">Edit</button>
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('Delete this team?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $team->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.teams.update', $team) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">Edit Team</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Department</label>
                                            <select name="department_id" class="form-select" required>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" @selected($team->department_id === $department->id)>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">UC</label>
                                            <select name="uc_id" class="form-select" required>
                                                @foreach ($ucs as $uc)
                                                    <option value="{{ $uc->id }}" @selected($team->uc_id === $uc->id)>{{ $uc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" value="{{ $team->name }}" class="form-control" required>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($team->is_active)>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No teams yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.teams.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Add Team</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="" selected disabled>Choose a department&hellip;</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">UC</label>
                            <select name="uc_id" class="form-select" required>
                                <option value="" selected disabled>Choose a UC&hellip;</option>
                                @foreach ($ucs as $uc)
                                    <option value="{{ $uc->id }}">{{ $uc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
