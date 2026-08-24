<div>
    <div class="card stat-card filter-bar p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small text-muted">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Meeting title...">
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">Status</label>
                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['upcoming', 'ongoing', 'completed', 'cancelled'] as $option)
                        <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">Project</label>
                <select wire:model.live="projectId" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">From</label>
                <input type="date" wire:model.live="from" class="form-control form-control-sm">
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label small text-muted">To</label>
                <input type="date" wire:model.live="to" class="form-control form-control-sm">
            </div>
            <div class="col-md-4 col-lg-1 d-flex justify-content-end gap-2">
                @if ($this->hasActiveFilters)
                    <button type="button" wire:click="resetFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </button>
                @endif
                @can('manage-meetings')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="card stat-card" wire:loading.class="is-loading-target" wire:target="search,status,projectId,from,to,resetFilters">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Organizer</th>
                    <th>Participants</th>
                    <th>Tasks</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($meetings as $meeting)
                    <tr wire:key="meeting-{{ $meeting->id }}">
                        <td>{{ $meeting->title }}</td>
                        <td class="text-muted small">{{ $meeting->meeting_date->toDateString() }} {{ $meeting->start_time }}&ndash;{{ $meeting->end_time }}</td>
                        <td class="text-muted small">{{ $meeting->project?->name ?? '—' }}</td>
                        <td>{{ $meeting->organizer->name }}</td>
                        <td>{{ $meeting->participants_count }}</td>
                        <td>{{ $meeting->tasks_count }}</td>
                        <td><x-status-badge :status="$meeting->status" /></td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.meetings.show', $meeting) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @can('manage-meetings')
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-id="{{ $meeting->id }}"
                                    data-title="{{ $meeting->title }}"
                                    data-type="{{ $meeting->type }}"
                                    data-location="{{ $meeting->location }}"
                                    data-description="{{ $meeting->description }}"
                                    data-agenda="{{ $meeting->agenda }}"
                                    data-organizer-id="{{ $meeting->organizer_id }}"
                                    data-status="{{ $meeting->status }}"
                                    data-project-id="{{ $meeting->project_id }}"
                                    data-form-template-id="{{ $meeting->form_template_id }}"
                                    data-meeting-date="{{ $meeting->meeting_date->toDateString() }}"
                                    data-start-time="{{ substr($meeting->start_time, 0, 5) }}"
                                    data-end-time="{{ substr($meeting->end_time, 0, 5) }}"
                                    data-participant-ids="{{ $meeting->participants->pluck('id')->implode(',') }}"
                                    onclick="openEditMeetingModal(this.dataset)">
                                    Edit
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-calendar-x"></i>
                                No meetings match these filters.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $meetings->links() }}</div>

    @can('manage-meetings')
        <div class="modal fade" id="createModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.meetings.store') }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">New Meeting</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select name="project_id" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attached Form (optional)</label>
                                <select name="form_template_id" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($formTemplates as $formTemplate)
                                        <option value="{{ $formTemplate->id }}">{{ $formTemplate->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <input type="text" name="type" class="form-control" placeholder="general">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="meeting_date" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Start</label>
                                    <input type="time" name="start_time" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">End</label>
                                    <input type="time" name="end_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Agenda</label>
                                <textarea name="agenda" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Organizer</label>
                                <select name="organizer_id" class="form-select">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="meetingIsRecurring">
                                <label class="form-check-label" for="meetingIsRecurring">Repeats</label>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Frequency</label>
                                    <select name="recurrence_frequency" class="form-select form-select-sm">
                                        <option value="weekly">Weekly</option>
                                        <option value="daily">Daily</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Every</label>
                                    <input type="number" name="recurrence_interval" value="1" min="1" max="52" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Until</label>
                                    <input type="date" name="recurrence_until" class="form-control form-control-sm">
                                </div>
                            </div>

                            @include('admin.partials.audience-picker', compact('nas', 'ucs', 'departments', 'teams', 'users'))
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Create Meeting</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editMeetingForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">Edit Meeting</h5></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="editMeetingTitle" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select name="project_id" id="editMeetingProject" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attached Form (optional)</label>
                                <select name="form_template_id" id="editMeetingFormTemplate" class="form-select">
                                    <option value="">— None —</option>
                                    @foreach ($formTemplates as $formTemplate)
                                        <option value="{{ $formTemplate->id }}">{{ $formTemplate->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <input type="text" name="type" id="editMeetingType" class="form-control" placeholder="general">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="meeting_date" id="editMeetingDate" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Start</label>
                                    <input type="time" name="start_time" id="editMeetingStart" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">End</label>
                                    <input type="time" name="end_time" id="editMeetingEnd" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="editMeetingStatus" class="form-select">
                                    @foreach (['upcoming', 'ongoing', 'completed', 'cancelled'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" id="editMeetingLocation" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="editMeetingDescription" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Agenda</label>
                                <textarea name="agenda" id="editMeetingAgenda" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Organizer</label>
                                <select name="organizer_id" id="editMeetingOrganizer" class="form-select">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="editMeetingIsRecurring">
                                <label class="form-check-label" for="editMeetingIsRecurring">Repeats</label>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Frequency</label>
                                    <select name="recurrence_frequency" class="form-select form-select-sm">
                                        <option value="weekly">Weekly</option>
                                        <option value="daily">Daily</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Every</label>
                                    <input type="number" name="recurrence_interval" value="1" min="1" max="52" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Until</label>
                                    <input type="date" name="recurrence_until" class="form-control form-control-sm">
                                </div>
                            </div>

                            <p class="text-muted small">Re-select who this meeting is for below — the audience is re-applied on save.</p>
                            @include('admin.partials.audience-picker', compact('nas', 'ucs', 'departments', 'teams', 'users'))
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const meetingUpdateUrlTemplate = @json(route('admin.meetings.update', ['scheduledMeeting' => '__ID__']));

            function openEditMeetingModal(data) {
                const form = document.getElementById('editMeetingForm');
                form.reset();
                form.action = meetingUpdateUrlTemplate.replace('__ID__', data.id);

                document.getElementById('editMeetingTitle').value = data.title ?? '';
                document.getElementById('editMeetingType').value = data.type ?? '';
                document.getElementById('editMeetingProject').value = data.projectId ?? '';
                document.getElementById('editMeetingFormTemplate').value = data.formTemplateId ?? '';
                document.getElementById('editMeetingDate').value = data.meetingDate ?? '';
                document.getElementById('editMeetingStart').value = data.startTime ?? '';
                document.getElementById('editMeetingEnd').value = data.endTime ?? '';
                document.getElementById('editMeetingLocation').value = data.location ?? '';
                document.getElementById('editMeetingDescription').value = data.description ?? '';
                document.getElementById('editMeetingAgenda').value = data.agenda ?? '';
                document.getElementById('editMeetingOrganizer').value = data.organizerId ?? '';
                document.getElementById('editMeetingStatus').value = data.status ?? 'upcoming';

                const scopeSelect = form.querySelector('.audience-scope-select');
                scopeSelect.value = 'individual';
                scopeSelect.dispatchEvent(new Event('change'));

                const participantIds = (data.participantIds ?? '').split(',').filter(Boolean);
                form.querySelectorAll('.audience-user-option input[type="checkbox"]').forEach((checkbox) => {
                    checkbox.checked = participantIds.includes(checkbox.value);
                });

                new bootstrap.Modal(document.getElementById('editModal')).show();
            }
        </script>
    @endcan
</div>
