<div class="card mb-3 meeting-row" data-index="{{ $index }}">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <h6 class="mb-0">Meeting</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-meeting">Remove</button>
        </div>

        @if ($meeting && $meeting->contact_id)
            <input type="hidden" name="meetings[{{ $index }}][contact_id]" value="{{ $meeting->contact_id }}">
            <p class="small text-muted mb-2">Contact: <strong>{{ $meeting->contact->name }}</strong> ({{ $meeting->contact->phone }})</p>
        @else
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small">Person Name</label>
                    <input type="text" name="meetings[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $meeting->contact->name ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Phone Number</label>
                    <input type="text" name="meetings[{{ $index }}][phone]" class="form-control form-control-sm" value="{{ $meeting->contact->phone ?? '' }}">
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label class="form-label small">CNIC (optional)</label>
                    <input type="text" name="meetings[{{ $index }}][cnic]" class="form-control form-control-sm" value="{{ $meeting->contact->cnic ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Address (optional)</label>
                    <input type="text" name="meetings[{{ $index }}][address]" class="form-control form-control-sm" value="{{ $meeting->contact->address ?? '' }}">
                </div>
            </div>
        @endif

        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label small">Meeting Title (optional)</label>
                <input type="text" name="meetings[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $meeting->title ?? '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small">Date &amp; Time</label>
                <input type="datetime-local" name="meetings[{{ $index }}][meeting_datetime]" class="form-control form-control-sm" value="{{ $meeting?->meeting_datetime?->format('Y-m-d\TH:i') }}">
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label small">Category</label>
            <select name="meetings[{{ $index }}][category]" class="form-select form-select-sm">
                @foreach (['general' => 'General Meeting', 'fund_discussion' => 'Fund Discussion', 'family_visit' => 'Family Visit', 'follow_up' => 'Follow-up', 'project_discussion' => 'Project Discussion', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(($meeting->category ?? 'general') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label small">Brief Discussion</label>
            <textarea name="meetings[{{ $index }}][discussion]" class="form-control form-control-sm" rows="2">{{ $meeting->discussion ?? '' }}</textarea>
        </div>

        <div class="form-check mb-2">
            <input type="checkbox" name="meetings[{{ $index }}][follow_up_required]" value="1" class="form-check-input" id="followup-{{ $index }}" @checked($meeting->follow_up_required ?? false)>
            <label class="form-check-label small" for="followup-{{ $index }}">Follow-up required</label>
        </div>

        <div class="mb-3">
            <label class="form-label small">Notes</label>
            <textarea name="meetings[{{ $index }}][notes]" class="form-control form-control-sm" rows="2">{{ $meeting->notes ?? '' }}</textarea>
        </div>

        <hr>

        <div class="form-check mb-2">
            <input type="checkbox" name="meetings[{{ $index }}][select_all_volunteers]" value="1" class="form-check-input select-all-toggle" id="selectall-{{ $index }}">
            <label class="form-check-label small" for="selectall-{{ $index }}">Select all volunteers</label>
        </div>

        <div class="participant-picker">
            <input type="text" class="form-control form-control-sm mb-2 participant-search" placeholder="Search volunteers...">
            <div class="participant-list border rounded p-2" style="max-height: 160px; overflow-y: auto;">
                @php $existingParticipantIds = $meeting?->participants->pluck('id')->all() ?? []; @endphp
                @forelse ($volunteers as $volunteer)
                    <div class="form-check participant-option">
                        <input type="checkbox" name="meetings[{{ $index }}][participant_ids][]" value="{{ $volunteer->id }}" class="form-check-input" id="participant-{{ $index }}-{{ $volunteer->id }}" @checked(in_array($volunteer->id, $existingParticipantIds))>
                        <label class="form-check-label small" for="participant-{{ $index }}-{{ $volunteer->id }}">{{ $volunteer->name }}</label>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No other volunteers available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
