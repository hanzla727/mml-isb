@extends('layouts.user')

@section('title', $report->exists ? 'Edit Report' : 'New Daily Report')

@section('content')
    <form method="POST" action="{{ $report->exists ? route('user.reports.update', $report) : route('user.reports.store') }}" id="report-form" style="padding-bottom: 90px;">
        @csrf
        @if ($report->exists) @method('PUT') @endif

        <input type="hidden" name="report_date" value="{{ old('report_date', $report->report_date instanceof \Carbon\Carbon ? $report->report_date->toDateString() : $report->report_date) }}">

        <div class="card stat-card p-4 mb-3">
            <h5 class="mb-3">Working Hours</h5>
            @if ($report->exists)
                <p class="text-muted small">Date: {{ $report->report_date->toDateString() }}</p>
            @endif
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Field Start Time</label>
                    <input type="time" id="field_start_time" name="field_start_time" value="{{ old('field_start_time', $report->field_start_time) }}" class="form-control" required>
                    @error('field_start_time') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Field End Time</label>
                    <input type="time" id="field_end_time" name="field_end_time" value="{{ old('field_end_time', $report->field_end_time) }}" class="form-control" required>
                    @error('field_end_time') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Total Hours</label>
                    <input type="text" id="total-hours-display" class="form-control" value="{{ $report->total_hours ?? '0.00' }}" readonly>
                </div>
            </div>
        </div>

        @if ($targets->isNotEmpty())
            <div class="card stat-card p-4 mb-3">
                <h5 class="mb-3">Assigned Tasks</h5>
                @foreach ($targets as $target)
                    @php
                        $current = (float) ($target->current_value ?? 0);
                        $goal = (float) $target->target_value;
                        $percentage = $goal > 0 ? min(100, round(($current / $goal) * 100, 1)) : 0;
                        $editable = $target->metric === 'custom';
                    @endphp
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $target->title }}</strong>
                            <span class="badge bg-secondary">{{ ucfirst($target->metric) }} &middot; {{ ucfirst($target->type) }}</span>
                        </div>
                        <div class="progress my-2" style="height: 8px;">
                            <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                        </div>
                        <input type="hidden" name="task_progress[{{ $loop->index }}][target_id]" value="{{ $target->id }}">

                        @if ($editable)
                            <div class="col-md-4 mb-2">
                                <label class="form-label small">Quantity Achieved</label>
                                <input type="number" step="0.01" min="0" name="task_progress[{{ $loop->index }}][current_value]" value="{{ $current }}" class="form-control form-control-sm">
                            </div>
                        @else
                            <div class="small text-muted mb-2">{{ $current }} / {{ $goal }} {{ $target->metric }} &mdash; auto-tracked from your reports</div>
                        @endif

                        <div class="form-check mb-2">
                            <input type="checkbox" name="task_progress[{{ $loop->index }}][is_completed]" value="1" class="form-check-input" id="task-completed-{{ $loop->index }}" @checked($target->is_completed ?? false)>
                            <label class="form-check-label small" for="task-completed-{{ $loop->index }}">Mark as completed</label>
                        </div>
                        <textarea name="task_progress[{{ $loop->index }}][notes]" class="form-control form-control-sm" rows="2" placeholder="Notes on this task">{{ $target->notes ?? '' }}</textarea>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card stat-card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Meetings</h5>
                <button type="button" id="add-meeting" class="btn btn-sm btn-primary">+ Add Meeting</button>
            </div>

            <div id="meetings-container">
                @foreach ($report->meetings ?? [] as $meeting)
                    @include('user.reports.partials.meeting-row', ['index' => $loop->index, 'meeting' => $meeting, 'volunteers' => $volunteers])
                @endforeach
            </div>
        </div>

        <div class="card stat-card p-4 mb-3">
            <h5 class="mb-3">Daily Summary</h5>
            <div class="mb-3">
                <label class="form-label">What did you achieve today?</label>
                <textarea name="summary" class="form-control" rows="3">{{ old('summary', $report->summary) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">What problems did you face?</label>
                <textarea name="challenges" class="form-control" rows="3">{{ old('challenges', $report->challenges) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">What will you do tomorrow?</label>
                <textarea name="tomorrow_plan" class="form-control" rows="3">{{ old('tomorrow_plan', $report->tomorrow_plan) }}</textarea>
            </div>
        </div>

        <div class="position-fixed bottom-0 start-0 end-0 bg-white border-top p-3 d-flex justify-content-end gap-2" style="z-index: 1030;">
            <a href="{{ route('user.reports.index') }}" class="btn btn-link">Cancel</a>
            <button type="submit" name="status" value="draft" class="btn btn-outline-secondary">Save Draft</button>
            <button type="submit" name="status" value="submitted" class="btn btn-primary">Submit Report</button>
        </div>
    </form>

    <template id="meeting-template">
        @include('user.reports.partials.meeting-row', ['index' => '__INDEX__', 'meeting' => null, 'volunteers' => $volunteers])
    </template>

    <script>
        (function () {
            let meetingIndex = {{ ($report->meetings ?? collect())->count() }};

            function updateTotalHours() {
                const start = document.getElementById('field_start_time').value;
                const end = document.getElementById('field_end_time').value;
                if (!start || !end) return;
                const [sh, sm] = start.split(':').map(Number);
                const [eh, em] = end.split(':').map(Number);
                let minutes = (eh * 60 + em) - (sh * 60 + sm);
                if (minutes < 0) minutes = 0;
                document.getElementById('total-hours-display').value = (minutes / 60).toFixed(2);
            }

            document.getElementById('field_start_time').addEventListener('input', updateTotalHours);
            document.getElementById('field_end_time').addEventListener('input', updateTotalHours);
            updateTotalHours();

            document.getElementById('add-meeting').addEventListener('click', function () {
                const template = document.getElementById('meeting-template').innerHTML;
                const html = template.split('__INDEX__').join(meetingIndex);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                document.getElementById('meetings-container').appendChild(wrapper.firstElementChild);
                meetingIndex++;
            });

            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-meeting')) {
                    e.target.closest('.meeting-row').remove();
                }
            });

            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('select-all-toggle')) {
                    const picker = e.target.closest('.card-body, .meeting-row').querySelector('.participant-picker');
                    picker.style.display = e.target.checked ? 'none' : '';
                }
            });

            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('participant-search')) {
                    const search = e.target.value.toLowerCase();
                    e.target.closest('.participant-picker').querySelectorAll('.participant-option').forEach((opt) => {
                        opt.style.display = opt.textContent.toLowerCase().includes(search) ? '' : 'none';
                    });
                }
            });
        })();
    </script>
@endsection
