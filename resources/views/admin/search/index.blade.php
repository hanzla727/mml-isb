@extends('layouts.admin')

@section('title', __('Search'))

@section('content')
    <form method="GET" class="card stat-card p-3 mb-3">
        <div class="input-group">
            <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="{{ __('Search contacts, tasks, reports, meetings, projects, NAs, UCs...') }}">
            <button class="btn btn-primary">{{ __('Search') }}</button>
        </div>
    </form>

    @if ($query !== '')
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('Contacts') }} ({{ $contacts->count() }})</h6>
                    @forelse ($contacts as $contact)
                        <div class="border-bottom py-2 small">{{ $contact->name }} &middot; {{ $contact->phone }}</div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('Tasks') }} ({{ $tasks->count() }})</h6>
                    @forelse ($tasks as $task)
                        <div class="border-bottom py-2 small">
                            <a href="{{ route('admin.tasks.show', $task) }}">{{ $task->title }}</a>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('Reports') }} ({{ $reports->count() }})</h6>
                    @forelse ($reports as $report)
                        <div class="border-bottom py-2 small">
                            <a href="{{ route('admin.reports.show', $report) }}">{{ $report->user->name }} &mdash; {{ $report->report_date->toDateString() }}</a>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('Meetings') }} ({{ $meetings->count() }})</h6>
                    @forelse ($meetings as $meeting)
                        <div class="border-bottom py-2 small">
                            <a href="{{ route('admin.meetings.show', $meeting) }}">{{ $meeting->title }}</a>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('Projects') }} ({{ $projects->count() }})</h6>
                    @forelse ($projects as $project)
                        <div class="border-bottom py-2 small">
                            <a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('NAs') }} ({{ $nas->count() }})</h6>
                    @forelse ($nas as $na)
                        <div class="border-bottom py-2 small">
                            <a href="{{ route('admin.nas.show', $na) }}">{{ $na->name }}</a>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-3">
                    <h6>{{ __('UCs') }} ({{ $ucs->count() }})</h6>
                    @forelse ($ucs as $uc)
                        <div class="border-bottom py-2 small">{{ $uc->name }}{{ $uc->sector ? ' ('.$uc->sector.')' : '' }}</div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No matches.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection
