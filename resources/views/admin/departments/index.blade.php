@extends('layouts.admin')

@section('title', __('Departments'))

@section('content')
    @can('manage-departments')
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">{{ __('Add Department') }}</button>
        </div>
    @endcan

    <p class="text-muted small">{{ __('Departments are shared across every UC — the same list applies organization-wide.') }}</p>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Users') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->users_count }}</td>
                        <td>
                            <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $department->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            @can('manage-departments')
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $department->id }}">{{ __('Edit') }}</button>
                                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('{{ __('Delete this department?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
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
                                    <div class="modal-header"><h5 class="modal-title">{{ __('Edit Department') }}</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Name') }}</label>
                                            <input type="text" name="name" value="{{ $department->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Description') }}</label>
                                            <textarea name="description" class="form-control">{{ $department->description }}</textarea>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($department->is_active)>
                                            <label class="form-check-label">{{ __('Active') }}</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No departments yet.') }}</td></tr>
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
                    <div class="modal-header"><h5 class="modal-title">{{ __('Add Department') }}</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection
