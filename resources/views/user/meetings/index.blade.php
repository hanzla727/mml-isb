@extends('layouts.user')

@section('title', 'Field Visits')

@section('content')
    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th></th><th>Date</th><th>Title / Category</th><th>Added By</th><th></th></tr></thead>
            <tbody>
                @forelse ($meetings as $meeting)
                    @php $isRead = (bool) $meeting->participants->first()?->pivot->read_at; @endphp
                    <tr class="{{ $isRead ? '' : 'fw-semibold' }}">
                        <td>
                            @unless ($isRead)
                                <span class="badge bg-primary">New</span>
                            @endunless
                        </td>
                        <td>{{ $meeting->meeting_datetime?->format('M j, Y g:i A') }}</td>
                        <td>{{ $meeting->title ?: str_replace('_', ' ', ucfirst($meeting->category)) }}</td>
                        <td>{{ $meeting->dailyReport->user->name }}</td>
                        <td><a href="{{ route('user.meetings.show', $meeting) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">You haven't been added to any field visit meetings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $meetings->links() }}</div>
@endsection
