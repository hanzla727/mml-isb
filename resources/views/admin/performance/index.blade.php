@extends('layouts.admin')

@section('title', __('Performance'))

@section('content')
    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Department') }}</th><th>{{ __('Team') }}</th><th>{{ __('Hours This Month') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($volunteers as $volunteer)
                    <tr>
                        <td>{{ $volunteer->name }}</td>
                        <td>{{ $volunteer->department?->name ?? '—' }}</td>
                        <td>{{ $volunteer->team?->name ?? '—' }}</td>
                        <td>{{ $volunteer->month_hours ?? 0 }}</td>
                        <td><a href="{{ route('admin.performance.show', $volunteer) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No volunteers found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
