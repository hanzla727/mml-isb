@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">Total Volunteers</div>
                    <div class="fs-4 fw-semibold">{{ $stats['total_users'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-journal-check"></i></div>
                <div>
                    <div class="text-muted small">Reports Today</div>
                    <div class="fs-4 fw-semibold">{{ $stats['today']['reports_submitted'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="text-muted small">Pending Reports</div>
                    <div class="fs-4 fw-semibold">{{ $stats['today']['pending_reports'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-person-plus"></i></div>
                <div>
                    <div class="text-muted small">New Contacts Today</div>
                    <div class="fs-4 fw-semibold">{{ $stats['today']['new_contacts'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="stat-icon bg-secondary-subtle text-secondary"><i class="bi bi-airplane"></i></div>
                <div>
                    <div class="text-muted small">On Leave Today</div>
                    <div class="fs-4 fw-semibold">{{ $stats['volunteers_on_leave'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-megaphone me-1 text-primary"></i> Active Projects</h6>
                @forelse ($stats['active_projects'] as $project)
                    <div class="d-flex justify-content-between align-items-center small border-bottom py-2">
                        <a href="{{ route('admin.projects.show', $project['id']) }}" class="text-decoration-none fw-medium">{{ $project['name'] }}</a>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress" style="width: 150px; height: 6px;">
                                <div class="progress-bar" style="width: {{ $project['progress'] }}%"></div>
                            </div>
                            <span class="text-muted" style="width: 2.5rem;">{{ $project['progress'] }}%</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-2"><i class="bi bi-megaphone"></i>No active projects.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-calendar3 me-1 text-primary"></i> Today</h6>
                <div class="text-muted small">Hours: <strong class="text-body">{{ $stats['today']['total_hours'] }}</strong></div>
                <div class="text-muted small">Field Visits: <strong class="text-body">{{ $stats['today']['total_meetings'] }}</strong></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-calendar-week me-1 text-primary"></i> This Week</h6>
                <div class="text-muted small">Hours: <strong class="text-body">{{ $stats['weekly']['total_hours'] }}</strong></div>
                <div class="text-muted small">Field Visits: <strong class="text-body">{{ $stats['weekly']['total_meetings'] }}</strong></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-calendar-month me-1 text-primary"></i> This Month</h6>
                <div class="text-muted small">Hours: <strong class="text-body">{{ $stats['monthly']['total_hours'] }}</strong></div>
                <div class="text-muted small">Field Visits: <strong class="text-body">{{ $stats['monthly']['total_meetings'] }}</strong></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-calendar-event me-1 text-primary"></i> Meetings</h6>
                <div class="text-muted small">Upcoming: <strong class="text-body">{{ $stats['meetings']['upcoming'] }}</strong></div>
                <div class="text-muted small">Today: <strong class="text-body">{{ $stats['meetings']['today'] }}</strong></div>
                <div class="text-muted small">Attendance rate: <strong class="text-body">{{ $stats['meetings']['attendance_rate'] }}%</strong></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-list-check me-1 text-primary"></i> Tasks</h6>
                <div class="text-muted small">Total assigned: <strong class="text-body">{{ $stats['tasks']['total_assigned'] }}</strong></div>
                <div class="text-muted small">Overdue: <strong class="text-danger">{{ $stats['tasks']['overdue'] }}</strong></div>
                <div class="text-muted small">Due today: <strong class="text-body">{{ $stats['tasks']['due_today'] }}</strong></div>
                <div class="text-muted small">Completion rate: <strong class="text-body">{{ $stats['tasks']['completion_rate'] }}%</strong></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-clipboard-check me-1 text-primary"></i> Task Reports</h6>
                <div class="text-muted small">Pending: <strong class="text-body">{{ $stats['reports']['pending'] }}</strong></div>
                <div class="text-muted small">Awaiting review: <strong class="text-body">{{ $stats['reports']['awaiting_review'] }}</strong></div>
                <div class="text-muted small">Approved: <strong class="text-success">{{ $stats['reports']['approved'] }}</strong></div>
                <div class="text-muted small">Rejected: <strong class="text-danger">{{ $stats['reports']['rejected'] }}</strong></div>
            </div>
        </div>
    </div>

    @hasanyrole('super_admin|admin|na_head')
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <div class="card stat-card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="bi bi-bar-chart-line me-1 text-primary"></i> Top Performing NAs</h6>
                        <a href="{{ route('admin.nas.compare') }}" class="small">Compare all NAs &rarr;</a>
                    </div>
                    @forelse ($topNas as $row)
                        <div class="d-flex justify-content-between align-items-center small border-bottom py-2">
                            <a href="{{ route('admin.nas.show', $row['na']) }}" class="text-decoration-none fw-medium">{{ $row['na']->name }}</a>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress" style="width: 150px; height: 6px;">
                                    <div class="progress-bar" style="width: {{ $row['score'] }}%"></div>
                                </div>
                                <span class="text-muted" style="width: 3.5rem;">{{ $row['score'] }} / 100</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-2"><i class="bi bi-geo-alt"></i>No NAs yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endhasanyrole

    <div class="row g-3">
        <div class="col-md-12">
            <div class="card stat-card p-3">
                <h6 class="mb-3"><i class="bi bi-journal-text me-1 text-primary"></i> Recent Reports</h6>
                @forelse ($stats['recent_reports'] as $report)
                    <div class="d-flex justify-content-between align-items-center small border-bottom py-2">
                        <a href="{{ route('admin.reports.show', $report) }}" class="text-decoration-none">{{ $report->user->name }} &mdash; {{ $report->report_date->toDateString() }}</a>
                        <x-status-badge :status="$report->review_status ?? $report->status" />
                    </div>
                @empty
                    <div class="empty-state py-2"><i class="bi bi-journal-text"></i>No reports yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
