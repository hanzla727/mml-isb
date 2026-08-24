@extends('layouts.user')

@section('title', $meeting->title)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <h5>{{ $meeting->title }}</h5>
        <p class="text-muted small">
            {{ $meeting->meeting_date->toDateString() }}, {{ $meeting->start_time }}&ndash;{{ $meeting->end_time }}
            @if ($meeting->location) &middot; {{ $meeting->location }} @endif
        </p>
        <div class="mb-2"><strong>Organizer:</strong> {{ $meeting->organizer->name }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $meeting->description ?: '—' }}</div>
        <div class="mb-2"><strong>Agenda:</strong> {{ $meeting->agenda ?: '—' }}</div>
        @if ($myAttendance)
            <div class="mb-2"><strong>Your Attendance:</strong> <span class="badge bg-secondary">{{ ucfirst($myAttendance->status) }}</span></div>
        @endif
    </div>

    @if ($meeting->tasks->isNotEmpty())
        <div class="card stat-card p-4">
            <h6 class="mb-3">Your Tasks From This Meeting</h6>
            @foreach ($meeting->tasks as $task)
                <div class="border-bottom py-2 d-flex justify-content-between">
                    <a href="{{ route('user.tasks.show', $task) }}">{{ $task->title }}</a>
                    <span class="badge bg-info text-dark">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
                </div>
            @endforeach
        </div>
    @endif
@endsection
