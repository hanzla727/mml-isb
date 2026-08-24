@extends('layouts.user')

@section('title', $task->title)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex justify-content-between">
            <h5>{{ $task->title }}</h5>
            <span class="badge bg-info text-dark">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span>
        </div>
        <p class="text-muted small">
            Due: {{ $task->due_date?->toDateString() ?? '—' }} {{ $task->due_time }}
            @if ($task->scheduledMeeting) &middot; From meeting: {{ $task->scheduledMeeting->title }} @endif
        </p>
        <div class="mb-2">{{ $task->description ?: '—' }}</div>
        <div class="mb-2"><strong>Notes:</strong> {{ $task->notes ?: '—' }}</div>
    </div>

    @if ($task->reports->isNotEmpty())
        <div class="card stat-card p-4 mb-3">
            <h6 class="mb-3">Your Submissions</h6>
            @foreach ($task->reports as $report)
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>Version {{ $report->version }}</strong>
                        <span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($report->review_status)) }}</span>
                    </div>
                    <div class="text-muted small">{{ $report->submitted_at?->format('M j, Y g:i A') }}</div>
                    @if ($report->amount_collected)
                        <div class="small mt-1">Amount collected: {{ number_format($report->amount_collected, 2) }}</div>
                    @endif
                    @if ($report->review_remarks)
                        <div class="alert alert-light border small mt-2 mb-0">Reviewer remarks: {{ $report->review_remarks }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($task->formTemplate)
        <div class="card stat-card p-4 mb-3">
            <h6 class="mb-3">{{ $task->formTemplate->name }}</h6>
            <p class="text-muted small">{{ $task->formTemplate->description }}</p>

            @if ($myFormSubmission)
                <div class="alert alert-light border small mb-0">
                    <strong>Submitted {{ $myFormSubmission->created_at->format('M j, Y g:i A') }}:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($myFormSubmission->values as $value)
                            <li>{{ $value->field->label }}: {{ $value->value ?? ($value->media_id ? 'file uploaded' : '—') }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <form method="POST" action="{{ route('user.tasks.submit-form-response', $task) }}" enctype="multipart/form-data">
                    @csrf
                    @foreach ($task->formTemplate->fields as $field)
                        <div class="mb-3">
                            <label class="form-label">{{ $field->label }} @if ($field->is_required)<span class="text-danger">*</span>@endif</label>
                            @switch($field->field_type)
                                @case('textarea')
                                    <textarea name="field_{{ $field->id }}" class="form-control" rows="2" @required($field->is_required)></textarea>
                                    @break
                                @case('dropdown')
                                    <select name="field_{{ $field->id }}" class="form-select" @required($field->is_required)>
                                        <option value="">— Select —</option>
                                        @foreach ($field->choices() as $choice)
                                            <option value="{{ $choice }}">{{ $choice }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('radio')
                                    @foreach ($field->choices() as $choice)
                                        <div class="form-check">
                                            <input type="radio" name="field_{{ $field->id }}" value="{{ $choice }}" class="form-check-input" @required($field->is_required)>
                                            <label class="form-check-label">{{ $choice }}</label>
                                        </div>
                                    @endforeach
                                    @break
                                @case('checkbox')
                                    @foreach ($field->choices() as $choice)
                                        <div class="form-check">
                                            <input type="checkbox" name="field_{{ $field->id }}[]" value="{{ $choice }}" class="form-check-input">
                                            <label class="form-check-label">{{ $choice }}</label>
                                        </div>
                                    @endforeach
                                    @break
                                @case('file')
                                @case('image')
                                    <input type="file" name="field_{{ $field->id }}" class="form-control" @required($field->is_required)>
                                    @break
                                @case('date')
                                    <input type="date" name="field_{{ $field->id }}" class="form-control" @required($field->is_required)>
                                    @break
                                @case('time')
                                    <input type="time" name="field_{{ $field->id }}" class="form-control" @required($field->is_required)>
                                    @break
                                @case('number')
                                    <input type="number" name="field_{{ $field->id }}" class="form-control" @required($field->is_required)>
                                    @break
                                @default
                                    <input type="text" name="field_{{ $field->id }}" class="form-control" @required($field->is_required)>
                            @endswitch
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary">Submit Form</button>
                </form>
            @endif
        </div>
    @endif

    @unless (in_array($task->status, ['approved', 'closed', 'cancelled']))
        <div class="card stat-card p-4">
            <h6 class="mb-3">{{ $task->reports->isNotEmpty() ? 'Resubmit Report' : 'Submit Report' }}</h6>
            <form method="POST" action="{{ route('user.tasks.submit-report', $task) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Work Summary</label>
                    <textarea name="work_summary" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Detailed Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Achievements</label>
                    <textarea name="achievements" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Problems Faced</label>
                    <textarea name="problems_faced" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Next Plan</label>
                    <textarea name="next_plan" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Working Hours</label>
                    <input type="number" step="0.5" min="0" name="working_hours" class="form-control" style="max-width: 200px;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount Collected (if any)</label>
                    <input type="number" step="0.01" min="0" name="amount_collected" class="form-control" style="max-width: 200px;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Attachments (receipts/documents)</label>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Report</button>
            </form>
        </div>
    @endunless
@endsection
