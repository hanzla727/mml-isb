@extends('layouts.admin')

@section('title', __('UCs'))

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">{{ __('Add UC') }}</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('NA') }}</th><th>{{ __('Sector') }}</th><th>{{ __('Teams') }}</th><th>{{ __('Volunteers') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($ucs as $uc)
                    <tr>
                        <td>{{ $uc->name }}</td>
                        <td class="text-muted small">{{ $uc->na->name }}</td>
                        <td class="text-muted small">{{ $uc->sector ?? '—' }}</td>
                        <td>{{ $uc->teams_count }}</td>
                        <td>{{ $uc->members_count }}</td>
                        <td><x-status-badge :status="$uc->status" /></td>
                        <td class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $uc->id }}">{{ __('Edit') }}</button>
                            <form method="POST" action="{{ route('admin.ucs.destroy', $uc) }}" onsubmit="return confirm('{{ __('Delete this UC?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $uc->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.ucs.update', $uc) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">{{ __('Edit UC') }}</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('NA') }}</label>
                                            <select name="na_id" class="form-select" required>
                                                @foreach ($nas as $na)
                                                    <option value="{{ $na->id }}" @selected($uc->na_id === $na->id)>{{ $na->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Name') }}</label>
                                            <input type="text" name="name" value="{{ $uc->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Sector') }} <span class="text-muted small">({{ __('optional') }})</span></label>
                                            <input type="text" name="sector" value="{{ $uc->sector }}" class="form-control" placeholder="e.g. F-10">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Description') }}</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $uc->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Status') }}</label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" @selected($uc->status === 'active')>{{ __('Active') }}</option>
                                                <option value="inactive" @selected($uc->status === 'inactive')>{{ __('Inactive') }}</option>
                                            </select>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No UCs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.ucs.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">{{ __('Add UC') }}</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('NA') }}</label>
                            <select name="na_id" class="form-select" required>
                                <option value="" selected disabled>{{ __('Choose an NA…') }}</option>
                                @foreach ($nas as $na)
                                    <option value="{{ $na->id }}">{{ $na->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Sector') }} <span class="text-muted small">({{ __('optional') }})</span></label>
                            <input type="text" name="sector" class="form-control" placeholder="e.g. F-10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
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
