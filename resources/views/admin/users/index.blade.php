@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                placeholder="Search name or email">
            <button class="btn btn-outline-secondary">Search</button>
        </form>
        @can('manage-users')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
        @endcan
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Pin</th>
                    <th>Role</th>
                    <th>UC</th>
                    <th>Department</th>
                    <th>Status</th>
                    @can('manage-users')
                    <th></th>@endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($user->avatar_path)
                                    <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-secondary fw-semibold" style="width:32px; height:32px; font-size:13px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                                    <div class="text-muted small">&commat;{{ $user?->username ?: '--' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->pin)
                                <span class="font-monospace" id="pinValue-{{ $user->id }}" data-pin="{{ $user->pin }}" data-masked="1">****</span>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="togglePinVisibility({{ $user->id }})" title="Show/Hide PIN">
                                    <i class="bi bi-eye" id="pinEyeIcon-{{ $user->id }}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="copyPin({{ $user->id }})" title="Copy PIN">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            @else
                                --
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $user->roles->pluck('name')->first() }}</span></td>
                        <td>{{ $user->uc?->name ?? '—' }}</td>
                        <td>{{ $user->department?->name ?? '—' }}</td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        @can('manage-users')
                            <td class="d-flex gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                    @csrf
                                    <button
                                        class="btn btn-sm btn-outline-secondary">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>

    <script>
        function togglePinVisibility(userId) {
            const el = document.getElementById('pinValue-' + userId);
            const icon = document.getElementById('pinEyeIcon-' + userId);
            const masked = el.dataset.masked === '1';
            el.textContent = masked ? el.dataset.pin : '****';
            el.dataset.masked = masked ? '0' : '1';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        function copyPin(userId) {
            navigator.clipboard.writeText(document.getElementById('pinValue-' + userId).dataset.pin);
        }
    </script>
@endsection