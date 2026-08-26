@extends('layouts.admin')

@section('title', $na->name)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">{{ $na->name }}</h5>
            </div>
            <x-status-badge :status="$na->status" />
        </div>
        <p class="mt-2 mb-2">{{ $na->description ?: '—' }}</p>
        <div class="text-muted small">
            <i class="bi bi-person-badge"></i> {{ __('NA Head') }}: {{ $na->naHead?->name ?? __('Unassigned') }}
            &middot; <i class="bi bi-diagram-3"></i> {{ $na->ucs->pluck('id')->count() }} {{ __('UCs') }}
        </div>

        <form method="GET" class="row g-2 align-items-end mt-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100">{{ __('Apply') }}</button>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-3">
        @php
            $tiles = [
                ['label' => __('Volunteers'), 'value' => $summary['active_volunteers'].' / '.$summary['total_volunteers'], 'icon' => 'people'],
                ['label' => __('Meetings Conducted'), 'value' => $summary['meetings_conducted'], 'icon' => 'calendar-event'],
                ['label' => __('Attendance Rate'), 'value' => $summary['attendance_rate'].'%', 'icon' => 'clipboard-check'],
                ['label' => __('Tasks Completed'), 'value' => $summary['tasks_completed'].' / '.$summary['tasks_assigned'], 'icon' => 'list-check'],
                ['label' => __('Reports Pending'), 'value' => $summary['reports_pending'], 'icon' => 'journal-text'],
                ['label' => __('Reports Approved'), 'value' => $summary['reports_approved'], 'icon' => 'journal-check'],
                ['label' => __('Working Hours'), 'value' => number_format($summary['working_hours'], 1), 'icon' => 'clock-history'],
                ['label' => __('Fund Collection'), 'value' => number_format($summary['fund_collection'], 2), 'icon' => 'cash-coin'],
                ['label' => __('Expenses'), 'value' => number_format($summary['expenses'], 2), 'icon' => 'receipt'],
                ['label' => __('Hospital Activities'), 'value' => $summary['hospital_activities'], 'icon' => 'hospital'],
                ['label' => __('Mosque Activities'), 'value' => $summary['mosque_activities'], 'icon' => 'building'],
                ['label' => __('Khidmat Activities'), 'value' => $summary['khidmat_activities'], 'icon' => 'hand-thumbs-up'],
                ['label' => __('Events Completed'), 'value' => $summary['events_completed'], 'icon' => 'stars'],
            ];
        @endphp
        @foreach ($tiles as $tile)
            <div class="col-md-3 col-lg-2">
                <div class="card stat-card p-3 h-100">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2"><i class="bi bi-{{ $tile['icon'] }}"></i></div>
                    <div class="fs-5 fw-semibold">{{ $tile['value'] }}</div>
                    <div class="text-muted small">{{ $tile['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card stat-card p-4 mb-3">
                <h6 class="mb-3">{{ __('UCs') }}</h6>
                @forelse ($na->ucs as $uc)
                    <div class="mb-2">
                        <strong>{{ $uc->name }}</strong>{{ $uc->sector ? ' ('.$uc->sector.')' : '' }}
                        <span class="text-muted small">— {{ $uc->members->count() }} {{ __('Volunteers') }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No UCs yet.') }}</p>
                @endforelse
            </div>

            <div class="card stat-card p-4 mb-3">
                <h6 class="mb-3">{{ __('Upcoming Meetings') }}</h6>
                @forelse ($upcomingMeetings as $meeting)
                    <div class="border-bottom py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.meetings.show', $meeting) }}">{{ $meeting->title }}</a>
                        <span class="text-muted small">{{ $meeting->meeting_date->toDateString() }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No upcoming meetings.') }}</p>
                @endforelse
            </div>

            <div class="card stat-card p-4">
                <h6 class="mb-3">{{ __('Recent Meetings') }}</h6>
                @forelse ($recentMeetings as $meeting)
                    <div class="border-bottom py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.meetings.show', $meeting) }}">{{ $meeting->title }}</a>
                        <span class="text-muted small">{{ $meeting->meeting_date->toDateString() }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No recent meetings.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="col-md-6">
            <div class="card stat-card p-4 mb-3">
                <h6 class="mb-3">{{ __('Pending Tasks') }}</h6>
                @forelse ($pendingTasks as $task)
                    <div class="border-bottom py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.tasks.show', $task) }}">{{ $task->title }}</a>
                        <x-status-badge :status="$task->status" />
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No pending tasks.') }}</p>
                @endforelse
            </div>

            <div class="card stat-card p-4 mb-3">
                <h6 class="mb-3">{{ __('Pending Reports') }}</h6>
                @forelse ($pendingReports as $report)
                    <div class="border-bottom py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.reports.show', $report) }}">{{ $report->user->name }}</a>
                        <span class="text-muted small">{{ $report->report_date->toDateString() }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No pending reports.') }}</p>
                @endforelse
            </div>

            <div class="card stat-card p-4">
                <h6 class="mb-3">{{ __('Announcements') }}</h6>
                @forelse ($announcements as $announcement)
                    <div class="border-bottom py-2">
                        <div class="fw-semibold">{{ $announcement->title }}</div>
                        <div class="text-muted small">{{ $announcement->published_at?->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No announcements.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
