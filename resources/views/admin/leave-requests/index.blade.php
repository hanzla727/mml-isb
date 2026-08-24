@extends('layouts.admin')

@section('title', 'Leave Requests')

@section('content')
    <div class="card stat-card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['pending', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Volunteer</th><th>Type</th><th>Dates</th><th>Reason</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($leaveRequests as $leaveRequest)
                    <tr>
                        <td>{{ $leaveRequest->user->name }}</td>
                        <td>{{ ucfirst($leaveRequest->leave_type) }}</td>
                        <td>{{ $leaveRequest->start_date->toDateString() }} &ndash; {{ $leaveRequest->end_date->toDateString() }}</td>
                        <td>{{ $leaveRequest->reason ?: '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $leaveRequest->status === 'approved' ? 'success' : ($leaveRequest->status === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($leaveRequest->status) }}
                            </span>
                        </td>
                        <td>
                            @if ($leaveRequest->status === 'pending')
                                <form method="POST" action="{{ route('admin.leave-requests.review', $leaveRequest) }}" class="d-flex gap-2">
                                    @csrf @method('PUT')
                                    <button name="decision" value="approve" class="btn btn-sm btn-outline-success">Approve</button>
                                    <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @else
                                <span class="text-muted small">by {{ $leaveRequest->reviewer?->name ?? '—' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $leaveRequests->links() }}</div>
@endsection
