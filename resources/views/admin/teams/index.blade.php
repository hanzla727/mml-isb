@extends('layouts.admin')

@section('title', __('Teams'))

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">{{ __('Add Team') }}</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Department') }}</th><th>{{ __('UC') }}</th><th>{{ __('Leader') }}</th><th>{{ __('Users') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($teams as $team)
                    <tr>
                        <td>{{ $team->name }}</td>
                        <td>{{ $team->department->name }}</td>
                        <td class="text-muted small">{{ $team->uc->name }}</td>
                        <td class="text-muted small">{{ $team->leader->name ?? '—' }}</td>
                        <td>{{ $team->users_count }}</td>
                        <td>
                            <span class="badge {{ $team->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $team->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $team->id }}">{{ __('Edit') }}</button>
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('{{ __('Delete this team?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $team->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.teams.update', $team) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">{{ __('Edit Team') }}</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Department') }}</label>
                                            <select name="department_id" class="form-select" required>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" @selected($team->department_id === $department->id)>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('UC') }}</label>
                                            <select name="uc_id" class="form-select" required>
                                                @foreach ($ucs as $uc)
                                                    <option value="{{ $uc->id }}" @selected($team->uc_id === $uc->id)>{{ $uc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Name') }}</label>
                                            <input type="text" name="name" value="{{ $team->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Team Leader') }}</label>
                                            <select name="leader_id" class="form-select">
                                                <option value="">{{ __('None') }}</option>
                                                @foreach ($teamLeaders as $leader)
                                                    <option value="{{ $leader->id }}" @selected($team->leader_id === $leader->id)>{{ $leader->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">{{ __('Only users with the Team Leader role appear here. The same person can lead more than one team.') }}</div>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($team->is_active)>
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
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No teams yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.teams.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">{{ __('Add Team') }}</h5></div>
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
                            <label class="form-label">{{ __('Team Leader') }}</label>
                            <select name="leader_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($teamLeaders as $leader)
                                    <option value="{{ $leader->id }}">{{ $leader->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('Only users with the Team Leader role appear here. The same person can lead more than one team.') }}</div>
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
