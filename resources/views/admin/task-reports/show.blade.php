@extends('layouts.admin')

@section('title', 'Review Report')

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $report->task->title }} &mdash; v{{ $report->version }}</h5>
            <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($report->review_status)) }}</span>
        </div>
        <p class="text-muted small">Submitted by {{ $report->user->name }} on {{ $report->submitted_at?->format('M j, Y g:i A') }}</p>

        <div class="row">
            <div class="col-md-6 mb-2"><strong>Work Summary:</strong> {{ $report->work_summary ?: '—' }}</div>
            <div class="col-md-6 mb-2"><strong>Working Hours:</strong> {{ $report->working_hours ?? '—' }}</div>
            <div class="col-md-6 mb-2"><strong>Amount Collected:</strong> {{ $report->amount_collected !== null ? number_format($report->amount_collected, 2) : '—' }}</div>
        </div>
        <div class="mb-2"><strong>Description:</strong> {{ $report->description ?: '—' }}</div>
        <div class="mb-2"><strong>Achievements:</strong> {{ $report->achievements ?: '—' }}</div>
        <div class="mb-2"><strong>Problems Faced:</strong> {{ $report->problems_faced ?: '—' }}</div>
        <div class="mb-2"><strong>Next Plan:</strong> {{ $report->next_plan ?: '—' }}</div>
        <div class="mb-2"><strong>Remarks:</strong> {{ $report->remarks ?: '—' }}</div>

        @if ($report->attachments->isNotEmpty())
            <div class="mb-2">
                <strong>Attachments:</strong>
                <ul>
                    @foreach ($report->attachments as $attachment)
                        <li><a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">{{ basename($attachment->path) }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($report->reviewed_at)
            <div class="alert alert-light border small">
                Reviewed by {{ $report->reviewer?->name }} on {{ $report->reviewed_at->format('M j, Y g:i A') }}.
                @if ($report->review_remarks)<br>Remarks: {{ $report->review_remarks }}@endif
            </div>
        @endif
    </div>

    @if ($previousVersions->isNotEmpty())
        <div class="card stat-card p-4 mb-3">
            <h6 class="mb-3">Previous Versions</h6>
            @foreach ($previousVersions as $previous)
                <div class="border-bottom py-2 small">
                    v{{ $previous->version }} &mdash; {{ str_replace('_', ' ', ucfirst($previous->review_status)) }}
                    ({{ $previous->submitted_at?->format('M j, Y') }})
                </div>
            @endforeach
        </div>
    @endif

    <div class="card stat-card p-4">
        <h6 class="mb-3">Review Decision</h6>
        <form method="POST" action="{{ route('admin.task-reports.review', $report) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Decision</label>
                <select name="decision" class="form-select" required>
                    <option value="approve">Approve</option>
                    <option value="approve_with_remarks">Approve With Remarks</option>
                    <option value="reject">Reject</option>
                    <option value="return_for_revision">Return for Revision</option>
                    <option value="request_more_information">Request More Information</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>
@endsection
