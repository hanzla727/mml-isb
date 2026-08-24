@extends('layouts.user')

@section('title', 'My Tasks')

@section('content')
    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Task</th><th>Meeting</th><th>Due Date</th><th>Priority</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->scheduledMeeting?->title ?? '—' }}</td>
                        <td>
                            {{ $task->due_date?->toDateString() ?? '—' }}
                            @if ($task->isOverdue())<span class="badge bg-danger">Overdue</span>@endif
                        </td>
                        <td><span class="badge bg-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($task->priority) }}</span></td>
                        <td><span class="badge bg-info text-dark">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></td>
                        <td><a href="{{ route('user.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No tasks assigned to you yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tasks->links() }}</div>
@endsection
