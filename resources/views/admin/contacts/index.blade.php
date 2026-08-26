@extends('layouts.admin')

@section('title', __('Contacts'))

@section('content')
    <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="max-width: 260px;"
            placeholder="{{ __('Search name or phone') }}">
        <select name="na_id" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
            <option value="">{{ __('NA') }}: {{ __('All') }}</option>
            @foreach ($nas as $na)
                <option value="{{ $na->id }}" @selected(request('na_id') == $na->id)>{{ $na->name }}</option>
            @endforeach
        </select>
        <select name="uc_id" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
            <option value="">{{ __('UC') }}: {{ __('All') }}</option>
            @foreach ($ucs as $uc)
                <option value="{{ $uc->id }}" @selected(request('uc_id') == $uc->id)>{{ $uc->name }} ({{ $uc->na->name }})</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary">{{ __('Search') }}</button>
        @if (request()->hasAny(['search', 'na_id', 'uc_id']))
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-link">{{ __('Cancel') }}</a>
        @endif
    </form>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('NA') }}</th>
                    <th>{{ __('UC') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Meetings') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contacts as $contact)
                    <tr>
                        <td>{{ $contact->name }}</td>
                        <td>{{ $contact->phone ?? '—' }}</td>
                        <td>{{ $contact->na?->name ?? '—' }}</td>
                        <td>{{ $contact->uc?->name ?? '—' }}</td>
                        <td>{{ $contact->creator?->name ?? '—' }}</td>
                        <td>{{ $contact->meetings_count }}</td>
                        <td><a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('No contacts found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $contacts->links() }}</div>
@endsection
