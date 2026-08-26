@extends('layouts.admin')

@section('title', __('Analytics'))

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>{{ __('Monthly Working Hours') }}</h6>
                <canvas id="hoursChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-3">
                <h6>{{ __('Monthly Field Visits') }}</h6>
                <canvas id="meetingsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="card stat-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ __('NA Ranking (Last 30 Days)') }}</h6>
            <a href="{{ route('admin.nas.compare') }}" class="small">{{ __('Full comparison') }} &rarr;</a>
        </div>
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>{{ __('NA') }}</th><th>{{ __('Score') }}</th></tr></thead>
            <tbody>
                @forelse ($naRanking as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('admin.nas.show', $row['na']) }}">{{ $row['na']->name }}</a></td>
                        <td>{{ $row['score'] }} / 100</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">{{ __('No NAs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const labels = @json($labels);

        new Chart(document.getElementById('hoursChart'), {
            type: 'line',
            data: { labels, datasets: [{ label: @json(__('Hours')), data: @json($monthlyHours), borderColor: '#4f46e5', tension: 0.3 }] },
        });

        new Chart(document.getElementById('meetingsChart'), {
            type: 'bar',
            data: { labels, datasets: [{ label: @json(__('Field Visits')), data: @json($monthlyMeetings), backgroundColor: '#4f46e5' }] },
        });
    </script>
@endsection
