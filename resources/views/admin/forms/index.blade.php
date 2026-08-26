@extends('layouts.admin')

@section('title', __('Form Templates'))

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.forms.create') }}" class="btn btn-primary">{{ __('New Form Template') }}</a>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Fields') }}</th><th>{{ __('Submissions') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($formTemplates as $formTemplate)
                    <tr>
                        <td><a href="{{ route('admin.forms.show', $formTemplate) }}">{{ $formTemplate->name }}</a></td>
                        <td>{{ $formTemplate->fields_count }}</td>
                        <td>{{ $formTemplate->submissions_count }}</td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.forms.edit', $formTemplate) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.forms.destroy', $formTemplate) }}" onsubmit="return confirm('{{ __('Delete this form template?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No form templates yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
