@extends('layouts.admin')

@section('title', 'Targets')

@section('content')
    @can('manage-targets')
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">New Target</button>
        </div>
    @endcan

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Title</th><th>Type</th><th>Metric</th><th>Value</th><th>Scope</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($targets as $target)
                    <tr>
                        <td>{{ $target->title }}</td>
                        <td>{{ ucfirst($target->type) }}</td>
                        <td>{{ ucfirst($target->metric) }}</td>
                        <td>{{ $target->target_value }}</td>
                        <td>{{ ucfirst($target->scope) }}</td>
                        <td>
                            <span class="badge {{ $target->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $target->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        @can('manage-targets')
                            <td>
                                <form method="POST" action="{{ route('admin.targets.destroy', $target) }}" onsubmit="return confirm('Delete this target?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No targets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $targets->links() }}</div>

    @can('manage-targets')
        <div class="modal fade" id="createModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.targets.store') }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">New Target</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Metric</label>
                                    <select name="metric" class="form-select">
                                        <option value="hours">Hours</option>
                                        <option value="meetings">Meetings</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Target Value</label>
                                <input type="number" step="0.01" name="target_value" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Scope</label>
                                <select name="scope" id="scope" class="form-select" onchange="toggleScopeTarget(this.value)">
                                    <option value="all">Everyone</option>
                                    <option value="department">Specific Department</option>
                                    <option value="team">Specific Team</option>
                                    <option value="user">Specific User</option>
                                </select>
                            </div>
                            <div class="mb-3 scope-target" data-scope="department" style="display:none;">
                                <label class="form-label">Department</label>
                                <select name="scope_id" class="form-select" disabled>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 scope-target" data-scope="team" style="display:none;">
                                <label class="form-label">Team</label>
                                <select name="scope_id" class="form-select" disabled>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 scope-target" data-scope="user" style="display:none;">
                                <label class="form-label">User</label>
                                <select name="scope_id" class="form-select" disabled>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <script>
                                function toggleScopeTarget(scope) {
                                    document.querySelectorAll('.scope-target').forEach((el) => {
                                        const active = el.dataset.scope === scope;
                                        el.style.display = active ? '' : 'none';
                                        el.querySelector('select').disabled = ! active;
                                    });
                                }
                            </script>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
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
