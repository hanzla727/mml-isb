@extends('layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
    <div class="card stat-card p-4" style="max-width: 640px;">
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Password {{ $user->exists ? '(leave blank to keep current)' : '' }}</label>
                <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}>
                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" id="roleSelect" class="form-select" required onchange="toggleRoleFields(this)">
                    @foreach (['super_admin' => 'Super Admin', 'admin' => 'Admin', 'na_head' => 'NA Head', 'team_leader' => 'Team Leader', 'user' => 'Volunteer'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->roles->pluck('name')->first()) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">An NA Head oversees every UC in their NA. Everyone else operates within one UC.</div>
            </div>

            <div class="mb-3" id="naIdsField">
                <label class="form-label">NAs Managed <span class="text-muted small">(Admin only — can be assigned several)</span></label>
                <select name="na_ids[]" class="form-select" multiple size="4">
                    @foreach ($nas as $na)
                        <option value="{{ $na->id }}" @selected(in_array($na->id, old('na_ids', $user->exists ? $user->adminNas->pluck('id')->all() : [])))>{{ $na->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3" id="naIdField">
                <label class="form-label">NA</label>
                <select name="na_id" class="form-select">
                    <option value="">None</option>
                    @foreach ($nas as $na)
                        <option value="{{ $na->id }}" @selected(old('na_id', $user->na_id) == $na->id)>{{ $na->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">The NA this NA Head is responsible for — every UC underneath it is their territory.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3" id="ucIdField">
                    <label class="form-label">UC</label>
                    <select name="uc_id" class="form-select">
                        <option value="">None</option>
                        @foreach ($ucs as $uc)
                            <option value="{{ $uc->id }}" @selected(old('uc_id', $user->uc_id) == $uc->id)>{{ $uc->name }} ({{ $uc->na->name }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Which UC this person operationally belongs to.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reporting Head</label>
                    <select name="reporting_head_id" class="form-select">
                        <option value="">None</option>
                        @foreach ($potentialHeads as $head)
                            <option value="{{ $head->id }}" @selected(old('reporting_head_id', $user->reporting_head_id) == $head->id)>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">None</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Team</label>
                    <select name="team_id" class="form-select">
                        <option value="">None</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $user->team_id) == $team->id)>{{ $team->name }} ({{ $team->uc->name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $user->is_active ?? true))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>

    <script>
        function toggleRoleFields(select) {
            document.getElementById('naIdsField').style.display = select.value === 'admin' ? '' : 'none';
            document.getElementById('naIdField').style.display = select.value === 'na_head' ? '' : 'none';
            document.getElementById('ucIdField').style.display = select.value === 'na_head' ? 'none' : '';
        }
        toggleRoleFields(document.getElementById('roleSelect'));
    </script>
@endsection
