@extends('layouts.admin')

@section('title', __('My Team'))

@section('content')
    <div class="card stat-card p-4 mb-3">
        <h5>{{ $teams->isNotEmpty() ? $teams->pluck('name')->join(', ') : __('My Team') }}</h5>
        <p class="text-muted small mb-0">{{ __(':count member(s)', ['count' => $members->count()]) }}</p>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Role') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td><a href="{{ route('admin.users.show', $member) }}">{{ $member->name }}</a></td>
                        <td>{{ $member->email }}</td>
                        <td><span class="badge bg-secondary">{{ $member->roles->pluck('name')->first() }}</span></td>
                        <td><a href="{{ route('admin.performance.show', $member) }}" class="btn btn-sm btn-outline-primary">{{ __('Performance') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No team members found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
