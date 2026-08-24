@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Monthly Working Hours</h6>
                <canvas id="hoursChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Monthly Meetings</h6>
                <canvas id="meetingsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="card stat-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">NA Ranking (Last 30 Days)</h6>
            <a href="{{ route('admin.nas.compare') }}" class="small">Full comparison &rarr;</a>
        </div>
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>NA</th><th>Score</th></tr></thead>
            <tbody>
                @forelse ($naRanking as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('admin.nas.show', $row['na']) }}">{{ $row['na']->name }}</a></td>
                        <td>{{ $row['score'] }} / 100</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No NAs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const labels = @json($labels);

        new Chart(document.getElementById('hoursChart'), {
            type: 'line',
            data: { labels, datasets: [{ label: 'Hours', data: @json($monthlyHours), borderColor: '#4f46e5', tension: 0.3 }] },
        });

        new Chart(document.getElementById('meetingsChart'), {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Meetings', data: @json($monthlyMeetings), backgroundColor: '#4f46e5' }] },
        });
    </script>
@endsection
