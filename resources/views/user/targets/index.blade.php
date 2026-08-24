@extends('layouts.user')

@section('title', 'My Targets')

@section('content')
    <div class="row g-3">
        @forelse ($targets as $target)
            @php
                $current = (float) ($target->current_value ?? 0);
                $goal = (float) $target->target_value;
                $percentage = $goal > 0 ? min(100, round(($current / $goal) * 100, 1)) : 0;
            @endphp
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <div class="d-flex justify-content-between">
                        <h6>{{ $target->title }}</h6>
                        <span class="badge bg-secondary">{{ ucfirst($target->type) }}</span>
                    </div>
                    <p class="text-muted small">{{ $target->description }}</p>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>{{ $current }} / {{ $goal }} {{ $target->metric }}</span>
                        <span>{{ $percentage }}%</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No targets assigned yet.</p>
        @endforelse
    </div>
@endsection
