@extends('layouts.user')

@section('title', 'My Leave Requests')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Request Leave</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Type</th><th>Dates</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($leaveRequests as $leaveRequest)
                    <tr>
                        <td>{{ ucfirst($leaveRequest->leave_type) }}</td>
                        <td>{{ $leaveRequest->start_date->toDateString() }} &ndash; {{ $leaveRequest->end_date->toDateString() }}</td>
                        <td>{{ $leaveRequest->reason ?: '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $leaveRequest->status === 'approved' ? 'success' : ($leaveRequest->status === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($leaveRequest->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No leave requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $leaveRequests->links() }}</div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('user.leave-requests.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Request Leave</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="leave_type" class="form-select" required>
                                <option value="sick">Sick</option>
                                <option value="personal">Personal</option>
                                <option value="vacation">Vacation</option>
                                <option value="emergency">Emergency</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
