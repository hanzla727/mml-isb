@extends('layouts.user')

@section('title', 'Meetings')

@section('content')
    <ul class="nav nav-tabs mb-3">
        @foreach (['upcoming' => 'Upcoming', 'today' => "Today's Meetings", 'past' => 'Past'] as $value => $label)
            <li class="nav-item">
                <a class="nav-link {{ $when === $value ? 'active' : '' }}" href="{{ route('user.schedule.index', ['when' => $value]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th></th><th>Title</th><th>Date</th><th>Organizer</th><th></th></tr></thead>
            <tbody>
                @forelse ($meetings as $meeting)
                    @php
                        $myParticipant = $meeting->participants->firstWhere('id', auth()->id());
                        $isRead = $myParticipant ? (bool) $myParticipant->pivot->read_at : true;
                    @endphp
                    <tr class="{{ $isRead ? '' : 'fw-semibold' }}">
                        <td>@unless ($isRead)<span class="badge bg-primary">New</span>@endunless</td>
                        <td>{{ $meeting->title }}</td>
                        <td>{{ $meeting->meeting_date->toDateString() }} {{ $meeting->start_time }}</td>
                        <td>{{ $meeting->organizer->name }}</td>
                        <td><a href="{{ route('user.schedule.show', $meeting) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No meetings here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $meetings->links() }}</div>
@endsection
