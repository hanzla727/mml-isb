@extends('layouts.admin')

@section('title', __('Leave Request'))

@section('content')
    <div class="card stat-card p-4" style="max-width: 640px;">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="mb-1">{{ ucfirst($leaveRequest->leave_type) }} {{ __('Leave') }}</h5>
                <p class="text-muted small mb-0">{{ $leaveRequest->user->name }}</p>
            </div>
            <span class="badge bg-{{ $leaveRequest->status === 'approved' ? 'success' : ($leaveRequest->status === 'rejected' ? 'danger' : 'secondary') }} fs-6">
                {{ __(ucfirst($leaveRequest->status)) }}
            </span>
        </div>

        <div class="mb-3">
            <div class="text-muted small">{{ __('Dates') }}</div>
            <div class="fs-5">{{ $leaveRequest->start_date->toDateString() }} &ndash; {{ $leaveRequest->end_date->toDateString() }}</div>
            <div class="text-muted small">{{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} {{ __('day(s)') }}</div>
        </div>

        @if ($leaveRequest->reason)
            <div class="mb-3">
                <div class="text-muted small">{{ __('Reason') }}</div>
                <div>{{ $leaveRequest->reason }}</div>
            </div>
        @endif

        @if ($leaveRequest->status !== 'pending')
            <div class="mb-3">
                <div class="text-muted small">{{ __('Reviewed by') }}</div>
                <div>{{ $leaveRequest->reviewer?->name ?? '—' }} &middot; {{ $leaveRequest->reviewed_at?->diffForHumans() }}</div>
            </div>
        @endif

        @if ($leaveRequest->status === 'pending')
            <form method="POST" action="{{ route('admin.leave-requests.review', $leaveRequest) }}" class="d-flex gap-2 mt-3">
                @csrf @method('PUT')
                <button name="decision" value="approve" class="btn btn-success">{{ __('Approve') }}</button>
                <button name="decision" value="reject" class="btn btn-outline-danger">{{ __('Reject') }}</button>
            </form>
        @endif

        <a href="{{ route('admin.leave-requests.index') }}" class="btn btn-link ps-0 mt-2">&larr; {{ __('Back to Leave Requests') }}</a>
    </div>
@endsection
