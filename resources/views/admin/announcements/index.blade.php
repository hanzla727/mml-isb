@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
    @can('manage-announcements')
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">New Announcement</button>
        </div>
    @endcan

    <div class="row g-3">
        @forelse ($announcements as $announcement)
            <div class="col-md-6">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">{{ $announcement->title }}</h6>
                        <span class="badge bg-secondary">{{ str_replace('_', ' ', $announcement->category) }}</span>
                    </div>
                    <p class="text-muted small mb-2">
                        {{ $announcement->creator->name }} &middot; {{ $announcement->published_at?->diffForHumans() }}
                    </p>
                    <p class="mb-2">{{ $announcement->body }}</p>
                    @can('manage-announcements')
                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-muted">No announcements yet.</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $announcements->links() }}</div>

    @can('manage-announcements')
        <div class="modal fade" id="createModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.announcements.store') }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">New Announcement</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Body</label>
                                <textarea name="body" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="general">General</option>
                                    <option value="meeting_reminder">Meeting Reminder</option>
                                    <option value="event">Event</option>
                                    <option value="deadline">Deadline</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Audience</label>
                                <select name="audience_scope" id="audience_scope" class="form-select" onchange="toggleAudienceTarget(this.value)">
                                    <option value="all">Everyone</option>
                                    <option value="department">Specific Department</option>
                                    <option value="team">Specific Team</option>
                                    <option value="user">Specific User</option>
                                </select>
                            </div>
                            <div class="mb-3 audience-target" data-scope="department" style="display:none;">
                                <label class="form-label">Department</label>
                                <select name="audience_id" class="form-select" disabled>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 audience-target" data-scope="team" style="display:none;">
                                <label class="form-label">Team</label>
                                <select name="audience_id" class="form-select" disabled>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 audience-target" data-scope="user" style="display:none;">
                                <label class="form-label">User</label>
                                <select name="audience_id" class="form-select" disabled>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <script>
                                function toggleAudienceTarget(scope) {
                                    document.querySelectorAll('.audience-target').forEach((el) => {
                                        const active = el.dataset.scope === scope;
                                        el.style.display = active ? '' : 'none';
                                        el.querySelector('select').disabled = ! active;
                                    });
                                }
                            </script>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Publish</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
