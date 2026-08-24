@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-muted small">Today's Status</div>
                <div class="fs-5 fw-semibold">
                    @if ($stats['today']['has_submitted'])
                        <span class="text-success">Submitted</span>
                    @else
                        <span class="text-warning">Not submitted yet</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-muted small">Hours Worked Today</div>
                <div class="fs-3 fw-semibold">{{ $stats['today']['hours_worked'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-muted small">Meetings Today</div>
                <div class="fs-3 fw-semibold">{{ $stats['today']['meetings_count'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>This Month</h6>
                <div class="text-muted small">Total Hours: <strong>{{ $stats['monthly']['total_hours'] }}</strong></div>
                <div class="text-muted small">Total Meetings: <strong>{{ $stats['monthly']['total_meetings'] }}</strong></div>
                <div class="text-muted small">Active Targets: <strong>{{ $stats['targets_count'] }}</strong></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Recent Announcements</h6>
                @forelse ($announcements as $notification)
                    <div class="border-bottom py-2 small">{{ $notification->data['title'] ?? 'Announcement' }}</div>
                @empty
                    <div class="text-muted small">No announcements yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Meetings</h6>
                <div class="text-muted small">Upcoming: <strong>{{ $stats['meetings']['upcoming'] }}</strong></div>
                <div class="text-muted small">Today: <strong>{{ $stats['meetings']['today'] }}</strong></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>My Tasks</h6>
                <div class="text-muted small">Due today: <strong>{{ $stats['tasks']['today'] }}</strong></div>
                <div class="text-muted small">Pending: <strong>{{ $stats['tasks']['pending'] }}</strong></div>
                <div class="text-muted small">Completed: <strong class="text-success">{{ $stats['tasks']['completed'] }}</strong></div>
                <div class="text-muted small">Rejected: <strong class="text-danger">{{ $stats['tasks']['rejected'] }}</strong></div>
                <div class="text-muted small">Needs revision: <strong class="text-warning">{{ $stats['tasks']['needs_revision'] }}</strong></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Leave Status</h6>
                <div class="text-muted small">Pending requests: <strong>{{ $stats['leave_status']['pending'] }}</strong></div>
                <div class="text-muted small">Upcoming approved leave: <strong>{{ $stats['leave_status']['upcoming_approved'] }}</strong></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>My Projects</h6>
                @forelse ($stats['assigned_projects'] as $project)
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span>{{ $project->name }}</span>
                        <span class="badge bg-secondary">{{ ucfirst($project->status) }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Not currently part of any project.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
