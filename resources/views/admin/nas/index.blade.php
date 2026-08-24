@extends('layouts.admin')

@section('title', 'NAs')

@section('content')
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('admin.nas.compare') }}" class="btn btn-outline-secondary"><i class="bi bi-bar-chart-line"></i> Compare NAs</a>
        @can('manage-nas')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg"></i> Add NA</button>
        @endcan
    </div>

    <div class="card stat-card">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>NA</th><th>NA Head</th><th>UCs</th><th>Volunteers</th><th>Performance Score</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($nas->sortByDesc('score')->values() as $index => $na)
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td><a href="{{ route('admin.nas.show', $na) }}">{{ $na->name }}</a></td>
                        <td>{{ $na->naHead?->name ?? '—' }}</td>
                        <td>{{ $na->ucs_count }}</td>
                        <td>{{ $na->members_count }}</td>
                        <td style="width: 160px;">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ $na->score }}%"></div>
                            </div>
                            <small class="text-muted">{{ $na->score }} / 100</small>
                        </td>
                        <td><x-status-badge :status="$na->status" /></td>
                        <td class="d-flex gap-2">
                            @can('manage-nas')
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $na->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.nas.destroy', $na) }}" onsubmit="return confirm('Delete this NA?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>

                    @can('manage-nas')
                        <div class="modal fade" id="editModal{{ $na->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.nas.update', $na) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title">Edit NA</h5></div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" value="{{ $na->name }}" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $na->description }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">NA Head</label>
                                                <select name="na_head_id" class="form-select">
                                                    <option value="">None</option>
                                                    @foreach ($potentialHeads as $head)
                                                        <option value="{{ $head->id }}" @selected($na->na_head_id === $head->id)>{{ $head->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="active" @selected($na->status === 'active')>Active</option>
                                                    <option value="inactive" @selected($na->status === 'inactive')>Inactive</option>
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
                    @endcan
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No NAs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('manage-nas')
        <div class="modal fade" id="createModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.nas.store') }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">Add NA</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NA Head</label>
                                <select name="na_head_id" class="form-select">
                                    <option value="">None (assign later)</option>
                                    @foreach ($potentialHeads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                </select>
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
    @endcan
@endsection
