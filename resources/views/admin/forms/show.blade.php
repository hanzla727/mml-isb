@extends('layouts.admin')

@section('title', $formTemplate->name)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <h5>{{ $formTemplate->name }}</h5>
        <p class="text-muted small mb-2">{{ $formTemplate->description ?: '—' }}</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($formTemplate->fields as $field)
                <span class="badge bg-secondary">{{ $field->label }} ({{ $field->field_type }}{{ $field->is_required ? ', required' : '' }})</span>
            @endforeach
        </div>
    </div>

    <div class="card stat-card p-4">
        <h6 class="mb-3">Submissions ({{ $submissions->count() }})</h6>
        @forelse ($submissions as $submission)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between">
                    <strong>{{ $submission->user->name }}</strong>
                    <span class="text-muted small">{{ $submission->created_at->format('M j, Y g:i A') }}</span>
                </div>
                <div class="text-muted small mb-2">
                    On: {{ class_basename($submission->submittable_type) }} &mdash; {{ $submission->submittable?->title ?? '—' }}
                </div>
                <ul class="mb-0 small">
                    @foreach ($submission->values as $value)
                        <li><strong>{{ $value->field->label }}:</strong> {{ $value->value ?? ($value->media ? basename($value->media->path) : '—') }}</li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-muted small mb-0">No submissions yet.</p>
        @endforelse
    </div>
@endsection
