@extends('layouts.admin')

@section('title', 'Report Detail')

@section('content')
    <div class="card stat-card p-4 mb-3">
        <h5>{{ $report->user->name }} &mdash; {{ $report->report_date->toDateString() }}</h5>
        <div class="row mt-3">
            <div class="col-md-3"><div class="text-muted small">Start</div>{{ $report->field_start_time }}</div>
            <div class="col-md-3"><div class="text-muted small">End</div>{{ $report->field_end_time }}</div>
            <div class="col-md-3"><div class="text-muted small">Total Hours</div>{{ $report->total_hours }}</div>
            <div class="col-md-3"><div class="text-muted small">Status</div>{{ ucfirst($report->status) }}</div>
        </div>
        @if ($report->review_status)
            <div class="mt-2">
                <span class="badge bg-info text-dark">Review: {{ str_replace('_', ' ', ucfirst($report->review_status)) }}</span>
                @if ($report->team_leader_id)
                    <span class="text-muted small ms-2">Team Leader: {{ $report->teamLeader?->name }}</span>
                @endif
            </div>
        @endif
        <hr>
        <div class="mb-2"><strong>Summary:</strong> {{ $report->summary ?: '—' }}</div>
        <div class="mb-2"><strong>Challenges:</strong> {{ $report->challenges ?: '—' }}</div>
        <div class="mb-2"><strong>Tomorrow's Plan:</strong> {{ $report->tomorrow_plan ?: '—' }}</div>

        @if ($report->team_leader_remarks)
            <div class="alert alert-light border small"><strong>Team Leader Remarks:</strong> {{ $report->team_leader_remarks }}</div>
        @endif
        @if ($report->admin_remarks)
            <div class="alert alert-light border small"><strong>Admin Remarks:</strong> {{ $report->admin_remarks }}</div>
        @endif
    </div>

    @can('reviewAsTeamLeader', $report)
        <div class="card stat-card p-4 mb-3">
            <h6 class="mb-3">Team Leader Review</h6>
            <form method="POST" action="{{ route('admin.reports.review', $report) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Decision</label>
                    <select name="decision" class="form-select" required>
                        <option value="recommend_approve">Recommend Approval</option>
                        <option value="needs_revision">Needs Revision</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
    @endcan

    @can('reviewAsAdmin', $report)
        <div class="card stat-card p-4 mb-3">
            <h6 class="mb-3">Admin Review</h6>
            <form method="POST" action="{{ route('admin.reports.review', $report) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Decision</label>
                    <select name="decision" class="form-select" required>
                        <option value="approve">Approve</option>
                        <option value="approve_with_remarks">Approve With Remarks</option>
                        <option value="reject">Reject</option>
                        <option value="needs_revision">Needs Revision</option>
                        <option value="close">Close</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
    @endcan

    <div class="card stat-card p-4">
        <h6 class="mb-3">Meetings (Field Visits) &mdash; {{ $report->meetings->count() }}</h6>
        @foreach ($report->meetings as $meeting)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between">
                    <strong>{{ $meeting->contact->name }}</strong>
                    <span class="badge bg-secondary">{{ str_replace('_', ' ', $meeting->category) }}</span>
                </div>
                <div class="text-muted small">{{ $meeting->contact->phone }}</div>
                <div class="mt-2">{{ $meeting->discussion }}</div>
                @if ($meeting->follow_up_required)
                    <span class="badge bg-warning text-dark mt-2">Follow-up required</span>
                @endif
                <div class="mt-2">
                    <div class="text-muted small">Participants (tagged teammates)</div>
                    @forelse ($meeting->participants as $participant)
                        <span class="badge bg-light text-dark border me-1">
                            {{ $participant->name }}
                            {{ $participant->pivot->read_at ? '(read)' : '(unread)' }}
                        </span>
                    @empty
                        <span class="text-muted small">None tagged</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@endsection
