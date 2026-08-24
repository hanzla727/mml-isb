@extends('layouts.admin')

@section('title', $formTemplate->exists ? 'Edit Form Template' : 'New Form Template')

@section('content')
    <form method="POST" action="{{ $formTemplate->exists ? route('admin.forms.update', $formTemplate) : route('admin.forms.store') }}">
        @csrf
        @if ($formTemplate->exists) @method('PUT') @endif

        <div class="card stat-card p-4 mb-3">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ $formTemplate->name }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ $formTemplate->description }}</textarea>
            </div>
        </div>

        <div class="card stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Fields</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addFieldRow">+ Add Field</button>
            </div>

            <div id="fieldRows">
                @forelse ($fields as $index => $field)
                    <div class="row g-2 align-items-center mb-2 field-row">
                        <div class="col-md-3">
                            <input type="text" name="fields[{{ $index }}][label]" value="{{ $field->label }}" class="form-control form-control-sm" placeholder="Label" required>
                        </div>
                        <div class="col-md-2">
                            <select name="fields[{{ $index }}][field_type]" class="form-select form-select-sm">
                                @foreach (['text', 'number', 'date', 'time', 'dropdown', 'checkbox', 'radio', 'textarea', 'file', 'image'] as $type)
                                    <option value="{{ $type }}" @selected($field->field_type === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="fields[{{ $index }}][options]" value="{{ implode(', ', $field->choices()) }}" class="form-control form-control-sm" placeholder="Options (comma-separated, for dropdown/radio/checkbox)">
                        </div>
                        <div class="col-md-2 form-check">
                            <input type="checkbox" name="fields[{{ $index }}][is_required]" value="1" class="form-check-input" @checked($field->is_required)>
                            <label class="form-check-label small">Required</label>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-field-row">&times;</button>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Form Template</button>
        </div>
    </form>

    <template id="fieldRowTemplate">
        <div class="row g-2 align-items-center mb-2 field-row">
            <div class="col-md-3">
                <input type="text" name="fields[__INDEX__][label]" class="form-control form-control-sm" placeholder="Label" required>
            </div>
            <div class="col-md-2">
                <select name="fields[__INDEX__][field_type]" class="form-select form-select-sm">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="time">Time</option>
                    <option value="dropdown">Dropdown</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio</option>
                    <option value="textarea">Textarea</option>
                    <option value="file">File</option>
                    <option value="image">Image</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="fields[__INDEX__][options]" class="form-control form-control-sm" placeholder="Options (comma-separated, for dropdown/radio/checkbox)">
            </div>
            <div class="col-md-2 form-check">
                <input type="checkbox" name="fields[__INDEX__][is_required]" value="1" class="form-check-input">
                <label class="form-check-label small">Required</label>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-field-row">&times;</button>
            </div>
        </div>
    </template>

    <script>
        (function () {
            let nextIndex = {{ count($fields) }};
            const rows = document.getElementById('fieldRows');
            const template = document.getElementById('fieldRowTemplate');

            document.getElementById('addFieldRow').addEventListener('click', function () {
                const html = template.innerHTML.replaceAll('__INDEX__', nextIndex++);
                rows.insertAdjacentHTML('beforeend', html);
            });

            rows.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-field-row')) {
                    e.target.closest('.field-row').remove();
                }
            });
        })();
    </script>
@endsection
