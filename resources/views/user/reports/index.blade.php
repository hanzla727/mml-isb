@extends('layouts.user')

@section('title', 'My Reports')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('user.reports.create') }}" class="btn btn-primary">New Report</a>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Hours</th><th>Meetings</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->report_date->toDateString() }}</td>
                        <td>{{ $report->total_hours }}</td>
                        <td>{{ $report->meetings_count }}</td>
                        <td>
                            <span class="badge {{ $report->status === 'draft' ? 'bg-secondary' : 'bg-success' }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td>
                            @if ($report->report_date->isToday())
                                <a href="{{ route('user.reports.edit', $report) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">
                        No reports yet. Click "New Report" to submit your first daily report.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $reports->links() }}</div>
@endsection
