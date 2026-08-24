@extends('layouts.admin')

@section('title', 'NA Comparison')

@section('content')
    <div class="card stat-card p-3 mb-3">
        <form method="GET" class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label small text-muted">Period</label>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="month" @selected($period === 'month')>Last 30 Days</option>
                    <option value="quarter" @selected($period === 'quarter')>Last Quarter</option>
                    <option value="year" @selected($period === 'year')>This Year</option>
                </select>
            </div>
        </form>
    </div>

    <div class="card stat-card p-4 mb-3">
        <h6 class="mb-3">NA Ranking</h6>
        <p class="text-muted small">A management tool, not a leaderboard — ranked by a configurable weighted score (task completion, report submission, attendance, and activity level). See <code>config/nas.php</code>.</p>
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>NA</th><th>Score</th><th>Task Completion</th><th>Attendance</th><th>Reports Submitted</th><th>Fund Collection</th></tr></thead>
            <tbody>
                @forelse ($comparison['rows'] as $index => $row)
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td><a href="{{ route('admin.nas.show', $row['na']) }}">{{ $row['na']->name }}</a></td>
                        <td><strong>{{ $row['score'] }}</strong> / 100</td>
                        <td>{{ $row['summary']['tasks_assigned'] > 0 ? round(($row['summary']['tasks_completed'] / $row['summary']['tasks_assigned']) * 100, 1) : 0 }}%</td>
                        <td>{{ $row['summary']['attendance_rate'] }}%</td>
                        <td>{{ $row['summary']['reports_submitted'] }}</td>
                        <td>{{ number_format($row['summary']['fund_collection'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No NAs to compare yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Task Completion Rate (%)</h6>
                <canvas id="taskCompletionChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Attendance Rate (%)</h6>
                <canvas id="attendanceChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Reports Submitted</h6>
                <canvas id="reportsChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Fund Collection</h6>
                <canvas id="fundChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const labels = @json($comparison['labels']);
        const colors = ['#4f46e5', '#0ea5e9', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];

        new Chart(document.getElementById('taskCompletionChart'), {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Task Completion %', data: @json($comparison['task_completion']), backgroundColor: colors }] },
        });
        new Chart(document.getElementById('attendanceChart'), {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Attendance %', data: @json($comparison['attendance']), backgroundColor: colors }] },
        });
        new Chart(document.getElementById('reportsChart'), {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Reports Submitted', data: @json($comparison['report_submission']), backgroundColor: colors }] },
        });
        new Chart(document.getElementById('fundChart'), {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Fund Collection', data: @json($comparison['fund_collection']), backgroundColor: colors }] },
        });
    </script>
@endsection
