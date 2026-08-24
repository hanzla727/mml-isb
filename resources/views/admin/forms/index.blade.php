@extends('layouts.admin')

@section('title', 'Form Templates')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.forms.create') }}" class="btn btn-primary">New Form Template</a>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Fields</th><th>Submissions</th><th></th></tr></thead>
            <tbody>
                @forelse ($formTemplates as $formTemplate)
                    <tr>
                        <td><a href="{{ route('admin.forms.show', $formTemplate) }}">{{ $formTemplate->name }}</a></td>
                        <td>{{ $formTemplate->fields_count }}</td>
                        <td>{{ $formTemplate->submissions_count }}</td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.forms.edit', $formTemplate) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.forms.destroy', $formTemplate) }}" onsubmit="return confirm('Delete this form template?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No form templates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
