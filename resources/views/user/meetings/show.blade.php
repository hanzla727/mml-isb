@extends('layouts.user')

@section('title', 'Field Visit Detail')

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $meeting->title ?: str_replace('_', ' ', ucfirst($meeting->category)) }}</h5>
            <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($meeting->category)) }}</span>
        </div>
        <p class="text-muted small mb-3">
            Added by {{ $meeting->dailyReport->user->name }} &middot;
            {{ $meeting->meeting_datetime?->format('M j, Y g:i A') }}
        </p>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="text-muted small">Person Met</div>
                <div>{{ $meeting->contact->name }}</div>
                <div class="text-muted small">{{ $meeting->contact->phone }}</div>
            </div>
            @if ($meeting->follow_up_required)
                <div class="col-md-6">
                    <span class="badge bg-warning text-dark">Follow-up required</span>
                </div>
            @endif
        </div>

        <div class="mb-2"><strong>Discussion:</strong> {{ $meeting->discussion ?: '—' }}</div>
        <div class="mb-2"><strong>Notes:</strong> {{ $meeting->notes ?: '—' }}</div>
    </div>

    <div class="card stat-card p-4">
        <h6 class="mb-3">Participants</h6>
        @forelse ($meeting->participants as $participant)
            <div class="d-flex justify-content-between border-bottom py-2">
                <span>{{ $participant->name }}</span>
                <span class="small {{ $participant->pivot->read_at ? 'text-success' : 'text-muted' }}">
                    {{ $participant->pivot->read_at ? 'Read' : 'Unread' }}
                </span>
            </div>
        @empty
            <p class="text-muted small mb-0">No participants recorded.</p>
        @endforelse
    </div>
@endsection
