@extends('layouts.admin')

@section('title', __('Announcements'))

@section('content')
    @can('manage-announcements')
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">{{ __('New Announcement') }}</button>
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
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-id="{{ $announcement->id }}"
                                data-title="{{ $announcement->title }}"
                                data-body="{{ $announcement->body }}"
                                data-category="{{ $announcement->category }}"
                                data-audience-scope="{{ $announcement->audience_scope }}"
                                data-audience-id="{{ $announcement->audience_id }}"
                                onclick="openEditAnnouncementModal(this.dataset)">
                                {{ __('Edit') }}
                            </button>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('{{ __('Delete this announcement?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-muted">{{ __('No announcements yet.') }}</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $announcements->links() }}</div>

    @can('manage-announcements')
        <div class="modal fade" id="createModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.announcements.store') }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">{{ __('New Announcement') }}</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Body') }}</label>
                                <textarea name="body" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Category') }}</label>
                                <select name="category" class="form-select">
                                    <option value="general">{{ __('General') }}</option>
                                    <option value="meeting_reminder">{{ __('Meeting Reminder') }}</option>
                                    <option value="event">{{ __('Event') }}</option>
                                    <option value="deadline">{{ __('Deadline') }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Audience') }}</label>
                                <select name="audience_scope" id="audience_scope" class="form-select" onchange="toggleAudienceTarget(this.value)">
                                    <option value="all">{{ __('Everyone') }}</option>
                                    <option value="department">{{ __('Specific Department') }}</option>
                                    <option value="user">{{ __('Specific User') }}</option>
                                </select>
                            </div>
                            <div class="mb-3 audience-target" data-scope="department" style="display:none;">
                                <label class="form-label">{{ __('Department') }}</label>
                                <select name="audience_id" class="form-select" disabled>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 audience-target" data-scope="user" style="display:none;">
                                <label class="form-label">{{ __('User') }}</label>
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
                            <button type="submit" class="btn btn-primary">{{ __('Publish') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editAnnouncementForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">{{ __('Edit Announcement') }}</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input type="text" name="title" id="editAnnouncementTitle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Body') }}</label>
                                <textarea name="body" id="editAnnouncementBody" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Category') }}</label>
                                <select name="category" id="editAnnouncementCategory" class="form-select">
                                    <option value="general">{{ __('General') }}</option>
                                    <option value="meeting_reminder">{{ __('Meeting Reminder') }}</option>
                                    <option value="event">{{ __('Event') }}</option>
                                    <option value="deadline">{{ __('Deadline') }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Audience') }}</label>
                                <select name="audience_scope" id="editAnnouncementScope" class="form-select" onchange="toggleEditAudienceTarget(this.value)">
                                    <option value="all">{{ __('Everyone') }}</option>
                                    <option value="department">{{ __('Specific Department') }}</option>
                                    <option value="user">{{ __('Specific User') }}</option>
                                </select>
                            </div>
                            <div class="mb-3 edit-audience-target" data-scope="department" style="display:none;">
                                <label class="form-label">{{ __('Department') }}</label>
                                <select name="audience_id" id="editAnnouncementDepartment" class="form-select" disabled>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 edit-audience-target" data-scope="user" style="display:none;">
                                <label class="form-label">{{ __('User') }}</label>
                                <select name="audience_id" id="editAnnouncementUser" class="form-select" disabled>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const announcementUpdateUrlTemplate = @json(route('admin.announcements.update', ['announcement' => '__ID__']));

            function toggleEditAudienceTarget(scope) {
                document.querySelectorAll('.edit-audience-target').forEach((el) => {
                    const active = el.dataset.scope === scope;
                    el.style.display = active ? '' : 'none';
                    el.querySelector('select').disabled = !active;
                });
            }

            function openEditAnnouncementModal(data) {
                const form = document.getElementById('editAnnouncementForm');
                form.action = announcementUpdateUrlTemplate.replace('__ID__', data.id);

                document.getElementById('editAnnouncementTitle').value = data.title ?? '';
                document.getElementById('editAnnouncementBody').value = data.body ?? '';
                document.getElementById('editAnnouncementCategory').value = data.category ?? 'general';
                document.getElementById('editAnnouncementScope').value = data.audienceScope ?? 'all';
                toggleEditAudienceTarget(data.audienceScope ?? 'all');

                if (data.audienceScope === 'department') {
                    document.getElementById('editAnnouncementDepartment').value = data.audienceId ?? '';
                } else if (data.audienceScope === 'user') {
                    document.getElementById('editAnnouncementUser').value = data.audienceId ?? '';
                }

                new bootstrap.Modal(document.getElementById('editModal')).show();
            }
        </script>
    @endcan
@endsection
