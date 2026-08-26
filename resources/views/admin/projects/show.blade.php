@extends('layouts.admin')

@section('title', $project->name)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $project->name }}</h5>
            <x-status-badge :status="$project->status" />
        </div>
        <p class="text-muted small mb-2">
            {{ $project->department->name }} &middot; {{ $project->uc->name }}
            &middot; {{ $project->start_date?->toDateString() ?? '—' }} &ndash; {{ $project->end_date?->toDateString() ?? '—' }}
        </p>
        <div class="mb-3">{{ $project->description ?: '—' }}</div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar" style="width: {{ $project->progress() }}%"></div>
        </div>
        <small class="text-muted">{{ __(':percent% of tasks completed', ['percent' => $project->progress()]) }}</small>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card stat-card p-4">
                <h6 class="mb-3">{{ __('Meetings') }} ({{ $project->meetings->count() }})</h6>
                @forelse ($project->meetings as $meeting)
                    <div class="border-bottom py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.meetings.show', $meeting) }}">{{ $meeting->title }}</a>
                        <span class="text-muted small">{{ $meeting->meeting_date->toDateString() }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No meetings linked to this project.') }}</p>
                @endforelse
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card p-4">
                <h6 class="mb-3">{{ __('Tasks') }} ({{ $project->tasks->count() }})</h6>
                @forelse ($project->tasks as $task)
                    <div class="border-bottom py-2 d-flex justify-content-between">
                        <a href="{{ route('admin.tasks.show', $task) }}">{{ $task->title }}</a>
                        <x-status-badge :status="$task->status" />
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No tasks linked to this project.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
