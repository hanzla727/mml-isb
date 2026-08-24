@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or email">
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
                    <th>Role</th>
                    <th>UC</th>
                    <th>Department</th>
                    <th>Status</th>
                    @can('manage-users')<th></th>@endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                        <td>{{ $user->email }}</td>
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
                                    <button class="btn btn-sm btn-outline-secondary">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
@endsection
