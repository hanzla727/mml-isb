@extends('layouts.admin')

@section('title', $task->title)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $task->title }}</h5>
            <div>
                <span class="badge bg-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($task->priority) }}</span>
                <span class="badge bg-info text-dark">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
            </div>
        </div>
        <p class="text-muted small">{{ __('Due') }}: {{ $task->due_date?->toDateString() ?? '—' }} {{ $task->due_time }}</p>
        <div class="mb-2">{{ $task->description ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Assigned To') }}:</strong> {{ $task->assignees->pluck('name')->join(', ') ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Notes') }}:</strong> {{ $task->notes ?: '—' }}</div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card stat-card p-4">
                <h6 class="mb-3">{{ __('Reports') }}</h6>
                @forelse ($task->reports as $report)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>v{{ $report->version }} &mdash; {{ $report->user->name }}</strong>
                            <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($report->review_status)) }}</span>
                        </div>
                        <div class="text-muted small">{{ $report->submitted_at?->format('M j, Y g:i A') }}</div>
                        <p class="mb-1 mt-2">{{ $report->work_summary }}</p>
                        <a href="{{ route('admin.task-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">{{ __('Review') }}</a>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No reports submitted yet.') }}</p>
                @endforelse
            </div>
        </div>
        <div class="col-md-5">
            <div class="card stat-card p-4">
                <h6 class="mb-3">{{ __('Timeline') }}</h6>
                <ul class="list-unstyled">
                    @forelse ($timeline as $entry)
                        <li class="mb-3 ps-3 border-start border-2">
                            <div class="small text-muted">{{ $entry->created_at->format('M j, Y g:i A') }}</div>
                            <div>{{ $entry->description }}{{ $entry->causer ? ' — ' . $entry->causer->name : '' }}</div>
                        </li>
                    @empty
                        <li class="text-muted small">{{ __('No activity recorded yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
