@extends('layouts.admin')

@section('title', $user->exists ? __('Edit User') : __('Add User'))

@section('content')
    <div class="card stat-card p-4" style="max-width: 640px;">
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Username') }} <span class="text-muted small">({{ __('optional — used for app login') }})</span></label>
                <div class="input-group">
                    <input type="text" name="username" id="usernameInput" value="{{ old('username', $user->username) }}" class="form-control">
                    <button type="button" class="btn btn-outline-secondary" onclick="generateUsername()">{{ __('Generate') }}</button>
                </div>
                @error('username') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Password') }} {{ $user->exists ? __('(leave blank to keep current)') : '' }}</label>
                <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}>
                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('PIN') }} <span class="text-muted small">({{ __('optional — 4 to 6 digits, used for app login') }})</span></label>
                <input type="text" name="pin" inputmode="numeric" pattern="[0-9]{4,6}" maxlength="6" class="form-control" value="{{ old('pin', $user->pin) }}">
                @error('pin') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Role') }}</label>
                <select name="role" id="roleSelect" class="form-select" required onchange="toggleRoleFields(this)">
                    @foreach (['super_admin' => __('Super Admin'), 'admin' => __('Admin'), 'na_head' => __('NA Head'), 'uc_head' => __('UC Head'), 'user' => __('Volunteer')] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->roles->pluck('name')->first()) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('An NA Head oversees every UC in their NA. A UC Head oversees one or more specific UCs. Everyone else operates within one UC.') }}</div>
            </div>

            <div class="mb-3" id="naIdsField">
                <label class="form-label">{{ __('NAs Managed') }} <span class="text-muted small">({{ __('Admin only — can be assigned several; tick as many as apply') }})</span></label>
                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                    @foreach ($nas as $na)
                        <div class="form-check">
                            <input type="checkbox" name="na_ids[]" value="{{ $na->id }}" class="form-check-input" id="na_id_{{ $na->id }}" @checked(in_array($na->id, old('na_ids', $user->exists ? $user->adminNas->pluck('id')->all() : [])))>
                            <label class="form-check-label" for="na_id_{{ $na->id }}">{{ $na->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-3" id="naIdField">
                <label class="form-label">{{ __('NA') }}</label>
                <select name="na_id" class="form-select">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($nas as $na)
                        <option value="{{ $na->id }}" @selected(old('na_id', $user->na_id) == $na->id)>{{ $na->name }}</option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('The NA this NA Head is responsible for — every UC underneath it is their territory.') }}</div>
            </div>

            <div class="mb-3" id="ucIdsField">
                <label class="form-label">{{ __('UCs Managed') }} <span class="text-muted small">({{ __('UC Head only — can be assigned several; tick as many as apply') }})</span></label>
                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                    @foreach ($ucs as $uc)
                        <div class="form-check">
                            <input type="checkbox" name="uc_ids[]" value="{{ $uc->id }}" class="form-check-input" id="uc_id_{{ $uc->id }}" @checked(in_array($uc->id, old('uc_ids', $user->exists ? $user->ucsHeaded->pluck('id')->all() : [])))>
                            <label class="form-check-label" for="uc_id_{{ $uc->id }}">{{ $uc->name }} ({{ $uc->na->name }})</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3" id="ucIdField">
                    <label class="form-label">{{ __('UC') }}</label>
                    <select name="uc_id" class="form-select">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($ucs as $uc)
                            <option value="{{ $uc->id }}" @selected(old('uc_id', $user->uc_id) == $uc->id)>{{ $uc->name }} ({{ $uc->na->name }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('Which UC this person operationally belongs to.') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Reporting Head') }}</label>
                    <select name="reporting_head_id" class="form-select">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($potentialHeads as $head)
                            <option value="{{ $head->id }}" @selected(old('reporting_head_id', $user->reporting_head_id) == $head->id)>{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Department') }}</label>
                <select name="department_id" class="form-select">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $user->is_active ?? true))>
                <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-link">{{ __('Cancel') }}</a>
        </form>
    </div>

    <script>
        function toggleRoleFields(select) {
            document.getElementById('naIdsField').style.display = select.value === 'admin' ? '' : 'none';
            document.getElementById('naIdField').style.display = select.value === 'na_head' ? '' : 'none';
            document.getElementById('ucIdsField').style.display = select.value === 'uc_head' ? '' : 'none';
            document.getElementById('ucIdField').style.display = (select.value === 'na_head' || select.value === 'uc_head') ? 'none' : '';
        }
        toggleRoleFields(document.getElementById('roleSelect'));

        function generateUsername() {
            const name = document.querySelector('input[name="name"]').value.trim().toLowerCase();
            const base = name.replace(/[^a-z0-9\s]/g, '').trim().replace(/\s+/g, '.') || 'user';
            const suffix = Math.floor(100 + Math.random() * 900);
            document.getElementById('usernameInput').value = `${base}${suffix}`;
        }
    </script>
@endsection
