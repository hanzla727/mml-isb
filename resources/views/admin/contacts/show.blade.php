@extends('layouts.admin')

@section('title', __('Contact'))

@section('content')
    <div class="card stat-card p-4 mb-3" style="max-width: 640px;">
        <div class="d-flex align-items-center gap-3 mb-3">
            @if ($contact->photo_path)
                <img src="{{ asset('storage/'.$contact->photo_path) }}" alt="" class="rounded-circle" width="56" height="56" style="object-fit: cover;">
            @else
                <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-secondary fw-semibold" style="width:56px; height:56px; font-size:20px;">
                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h4 class="mb-0">{{ $contact->name }}</h4>
                <div class="text-muted small">{{ __('Created By') }}: {{ $contact->creator?->name ?? '—' }}</div>
            </div>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Phone') }}</dt>
            <dd class="col-sm-9">{{ $contact->phone ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('CNIC') }}</dt>
            <dd class="col-sm-9">{{ $contact->cnic ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('Address') }}</dt>
            <dd class="col-sm-9">{{ $contact->address ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('NA') }}</dt>
            <dd class="col-sm-9">{{ $contact->na?->name ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('UC') }}</dt>
            <dd class="col-sm-9">{{ $contact->uc?->name ?? '—' }}</dd>

            @if ($contact->notes)
                <dt class="col-sm-3">{{ __('Notes') }}</dt>
                <dd class="col-sm-9">{{ $contact->notes }}</dd>
            @endif
        </dl>
    </div>

    <h5>{{ __('Meeting History') }} ({{ $contact->meetings->count() }})</h5>
    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Volunteer') }}</th>
                    <th>{{ __('Title') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contact->meetings as $meeting)
                    <tr>
                        <td>{{ $meeting->meeting_datetime }}</td>
                        <td>{{ $meeting->dailyReport?->user?->name ?? '—' }}</td>
                        <td>{{ $meeting->title ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">{{ __('No meetings logged with this contact yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.contacts.index') }}" class="btn btn-link mt-3">{{ __('Back to Contacts') }}</a>
@endsection
