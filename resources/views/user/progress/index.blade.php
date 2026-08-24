@extends('layouts.user')

@section('title', 'My Progress')

@section('content')
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Working Hours (Last 6 Months)</h6>
                <canvas id="hoursChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>Field Visits (Last 6 Months)</h6>
                <canvas id="meetingsChart" height="200"></canvas>
            </div>
        </div>
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
            data: { labels, datasets: [{ label: 'Field Visits', data: @json($monthlyMeetings), backgroundColor: '#4f46e5' }] },
        });
    </script>
@endsection
