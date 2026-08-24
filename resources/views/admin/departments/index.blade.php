@extends('layouts.admin')

@section('title', 'Departments')

@section('content')
    @can('manage-departments')
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add Department</button>
        </div>
    @endcan

    <p class="text-muted small">Departments are shared across every UC &mdash; the same list applies organization-wide. To assign a department to a specific UC, create a Team for it under that UC.</p>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Teams</th><th>Users</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->teams_count }}</td>
                        <td>{{ $department->users_count }}</td>
                        <td>
                            <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            @can('manage-departments')
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $department->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>

                    @can('manage-departments')
                    <div class="modal fade" id="editModal{{ $department->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">Edit Department</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" value="{{ $department->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control">{{ $department->description }}</textarea>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($department->is_active)>
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
                    @endcan
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No departments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('manage-departments')
    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.departments.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Add Department</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection
