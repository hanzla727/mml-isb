@extends('layouts.admin')

@section('title', $volunteer->name . ' — ' . __('Performance'))

@section('content')
    <form method="GET" class="card stat-card p-3 mb-3 row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">{{ __('From') }}</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('To') }}</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary w-100">{{ __('Apply') }}</button>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['total_hours'] }}</div>
                <div class="text-muted small">{{ __('Total Hours') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['reports']['approved'] }} / {{ $summary['reports']['submitted'] }}</div>
                <div class="text-muted small">{{ __('Reports Approved / Submitted') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['meetings']['attendance_rate'] }}%</div>
                <div class="text-muted small">{{ __('Meeting Attendance Rate') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['tasks']['completed'] }} / {{ $summary['tasks']['assigned'] }}</div>
                <div class="text-muted small">{{ __('Tasks Completed / Assigned') }}</div>
            </div>
        </div>
    </div>

    <div class="card stat-card p-3">
        <h6>{{ __('Monthly Working Hours (Last 6 Months)') }}</h6>
        <canvas id="hoursChart" height="200"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        new Chart(document.getElementById('hoursChart'), {
            type: 'line',
            data: {
                labels: @json($trend['labels']),
                datasets: [{ label: @json(__('Hours')), data: @json($trend['hours']), borderColor: '#4f46e5', tension: 0.3 }],
            },
        });
    </script>
@endsection
