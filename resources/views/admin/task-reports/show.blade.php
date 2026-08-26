@extends('layouts.admin')

@section('title', __('Review Report'))

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $report->task->title }} &mdash; v{{ $report->version }}</h5>
            <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($report->review_status)) }}</span>
        </div>
        <p class="text-muted small">{{ __('Submitted by :name on :date', ['name' => $report->user->name, 'date' => $report->submitted_at?->format('M j, Y g:i A')]) }}</p>

        <div class="row">
            <div class="col-md-6 mb-2"><strong>{{ __('Work Summary') }}:</strong> {{ $report->work_summary ?: '—' }}</div>
            <div class="col-md-6 mb-2"><strong>{{ __('Working Hours') }}:</strong> {{ $report->working_hours ?? '—' }}</div>
            <div class="col-md-6 mb-2"><strong>{{ __('Amount Collected') }}:</strong> {{ $report->amount_collected !== null ? number_format($report->amount_collected, 2) : '—' }}</div>
        </div>
        <div class="mb-2"><strong>{{ __('Description') }}:</strong> {{ $report->description ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Achievements') }}:</strong> {{ $report->achievements ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Problems Faced') }}:</strong> {{ $report->problems_faced ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Next Plan') }}:</strong> {{ $report->next_plan ?: '—' }}</div>
        <div class="mb-2"><strong>{{ __('Remarks') }}:</strong> {{ $report->remarks ?: '—' }}</div>

        @if ($report->attachments->isNotEmpty())
            <div class="mb-2">
                <strong>{{ __('Attachments') }}:</strong>
                <ul>
                    @foreach ($report->attachments as $attachment)
                        <li><a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">{{ basename($attachment->path) }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($report->reviewed_at)
            <div class="alert alert-light border small">
                {{ __('Reviewed by :name on :date.', ['name' => $report->reviewer?->name, 'date' => $report->reviewed_at->format('M j, Y g:i A')]) }}
                @if ($report->review_remarks)<br>{{ __('Remarks') }}: {{ $report->review_remarks }}@endif
            </div>
        @endif
    </div>

    @if ($previousVersions->isNotEmpty())
        <div class="card stat-card p-4 mb-3">
            <h6 class="mb-3">{{ __('Previous Versions') }}</h6>
            @foreach ($previousVersions as $previous)
                <div class="border-bottom py-2 small">
                    v{{ $previous->version }} &mdash; {{ str_replace('_', ' ', ucfirst($previous->review_status)) }}
                    ({{ $previous->submitted_at?->format('M j, Y') }})
                </div>
            @endforeach
        </div>
    @endif

    <div class="card stat-card p-4">
        <h6 class="mb-3">{{ __('Review Decision') }}</h6>
        <form method="POST" action="{{ route('admin.task-reports.review', $report) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ __('Decision') }}</label>
                <select name="decision" class="form-select" required>
                    <option value="approve">{{ __('Approve') }}</option>
                    <option value="approve_with_remarks">{{ __('Approve With Remarks') }}</option>
                    <option value="reject">{{ __('Reject') }}</option>
                    <option value="return_for_revision">{{ __('Return for Revision') }}</option>
                    <option value="request_more_information">{{ __('Request More Information') }}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Remarks') }}</label>
                <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Submit Review') }}</button>
        </form>
    </div>
@endsection
