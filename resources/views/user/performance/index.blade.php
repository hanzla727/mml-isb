@extends('layouts.user')

@section('title', 'My Performance')

@section('content')
    <p class="text-muted small mb-3">A personal summary for the last 30 days, for your own awareness and your reviewer's evaluation.</p>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['total_hours'] }}</div>
                <div class="text-muted small">Total Hours</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['reports']['approved'] }} / {{ $summary['reports']['submitted'] }}</div>
                <div class="text-muted small">Reports Approved / Submitted</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['meetings']['attendance_rate'] }}%</div>
                <div class="text-muted small">Meeting Attendance Rate</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="fs-4 fw-bold">{{ $summary['tasks']['completed'] }} / {{ $summary['tasks']['assigned'] }}</div>
                <div class="text-muted small">Tasks Completed / Assigned</div>
            </div>
        </div>
    </div>

    <div class="card stat-card p-3">
        <h6>Monthly Working Hours (Last 6 Months)</h6>
        <canvas id="hoursChart" height="200"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        new Chart(document.getElementById('hoursChart'), {
            type: 'line',
            data: {
                labels: @json($trend['labels']),
                datasets: [{ label: 'Hours', data: @json($trend['hours']), borderColor: '#4f46e5', tension: 0.3 }],
            },
        });
    </script>
@endsection
