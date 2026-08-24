@extends('layouts.admin')

@section('title', 'UCs')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add UC</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>NA</th><th>Sector</th><th>Teams</th><th>Volunteers</th><th>Status</th><th></th></tr></thead>
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
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $uc->id }}">Edit</button>
                            <form method="POST" action="{{ route('admin.ucs.destroy', $uc) }}" onsubmit="return confirm('Delete this UC?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $uc->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.ucs.update', $uc) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h5 class="modal-title">Edit UC</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">NA</label>
                                            <select name="na_id" class="form-select" required>
                                                @foreach ($nas as $na)
                                                    <option value="{{ $na->id }}" @selected($uc->na_id === $na->id)>{{ $na->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" value="{{ $uc->name }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Sector <span class="text-muted small">(optional)</span></label>
                                            <input type="text" name="sector" value="{{ $uc->sector }}" class="form-control" placeholder="e.g. F-10">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $uc->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" @selected($uc->status === 'active')>Active</option>
                                                <option value="inactive" @selected($uc->status === 'inactive')>Inactive</option>
                                            </select>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">No UCs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.ucs.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Add UC</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NA</label>
                            <select name="na_id" class="form-select" required>
                                <option value="" selected disabled>Choose an NA&hellip;</option>
                                @foreach ($nas as $na)
                                    <option value="{{ $na->id }}">{{ $na->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sector <span class="text-muted small">(optional)</span></label>
                            <input type="text" name="sector" class="form-control" placeholder="e.g. F-10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
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
