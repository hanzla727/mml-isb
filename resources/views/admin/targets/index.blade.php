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
                            <td class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-id="{{ $target->id }}"
                                    data-title="{{ $target->title }}"
                                    data-description="{{ $target->description }}"
                                    data-type="{{ $target->type }}"
                                    data-metric="{{ $target->metric }}"
                                    data-target-value="{{ $target->target_value }}"
                                    data-scope="{{ $target->scope }}"
                                    data-scope-id="{{ $target->scope_id }}"
                                    data-start-date="{{ $target->start_date?->toDateString() }}"
                                    data-end-date="{{ $target->end_date?->toDateString() }}"
                                    data-is-active="{{ $target->is_active ? '1' : '0' }}"
                                    onclick="openEditTargetModal(this.dataset)">
                                    Edit
                                </button>
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

        <div class="modal fade" id="editModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editTargetForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">Edit Target</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="editTargetTitle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="editTargetDescription" class="form-control"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" id="editTargetType" class="form-select">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Metric</label>
                                    <select name="metric" id="editTargetMetric" class="form-select">
                                        <option value="hours">Hours</option>
                                        <option value="meetings">Meetings</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Target Value</label>
                                <input type="number" step="0.01" name="target_value" id="editTargetValue" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Scope</label>
                                <select name="scope" id="editTargetScope" class="form-select" onchange="toggleEditScopeTarget(this.value)">
                                    <option value="all">Everyone</option>
                                    <option value="department">Specific Department</option>
                                    <option value="team">Specific Team</option>
                                    <option value="user">Specific User</option>
                                </select>
                            </div>
                            <div class="mb-3 edit-scope-target" data-scope="department" style="display:none;">
                                <label class="form-label">Department</label>
                                <select name="scope_id" id="editTargetDepartment" class="form-select" disabled>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 edit-scope-target" data-scope="team" style="display:none;">
                                <label class="form-label">Team</label>
                                <select name="scope_id" id="editTargetTeam" class="form-select" disabled>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 edit-scope-target" data-scope="user" style="display:none;">
                                <label class="form-label">User</label>
                                <select name="scope_id" id="editTargetUser" class="form-select" disabled>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="editTargetStartDate" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="editTargetEndDate" class="form-control">
                                </div>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" id="editTargetIsActive" class="form-check-input">
                                <label class="form-check-label" for="editTargetIsActive">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const targetUpdateUrlTemplate = @json(route('admin.targets.update', ['target' => '__ID__']));

            function toggleEditScopeTarget(scope) {
                document.querySelectorAll('.edit-scope-target').forEach((el) => {
                    const active = el.dataset.scope === scope;
                    el.style.display = active ? '' : 'none';
                    el.querySelector('select').disabled = !active;
                });
            }

            function openEditTargetModal(data) {
                const form = document.getElementById('editTargetForm');
                form.action = targetUpdateUrlTemplate.replace('__ID__', data.id);

                document.getElementById('editTargetTitle').value = data.title ?? '';
                document.getElementById('editTargetDescription').value = data.description ?? '';
                document.getElementById('editTargetType').value = data.type ?? 'daily';
                document.getElementById('editTargetMetric').value = data.metric ?? 'hours';
                document.getElementById('editTargetValue').value = data.targetValue ?? '';
                document.getElementById('editTargetStartDate').value = data.startDate ?? '';
                document.getElementById('editTargetEndDate').value = data.endDate ?? '';
                document.getElementById('editTargetIsActive').checked = data.isActive === '1';

                document.getElementById('editTargetScope').value = data.scope ?? 'all';
                toggleEditScopeTarget(data.scope ?? 'all');

                if (data.scope === 'department') {
                    document.getElementById('editTargetDepartment').value = data.scopeId ?? '';
                } else if (data.scope === 'team') {
                    document.getElementById('editTargetTeam').value = data.scopeId ?? '';
                } else if (data.scope === 'user') {
                    document.getElementById('editTargetUser').value = data.scopeId ?? '';
                }

                new bootstrap.Modal(document.getElementById('editModal')).show();
            }
        </script>
    @endcan
@endsection
