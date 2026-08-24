@extends('layouts.admin')

@section('title', 'Report Review Queue')

@section('content')
    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Task</th><th>Volunteer</th><th>Version</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->task->title }}</td>
                        <td>{{ $report->user->name }}</td>
                        <td>v{{ $report->version }}</td>
                        <td>{{ $report->submitted_at?->format('M j, Y g:i A') }}</td>
                        <td><span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($report->review_status)) }}</span></td>
                        <td><a href="{{ route('admin.task-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Nothing awaiting review.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $reports->links() }}</div>
@endsection
