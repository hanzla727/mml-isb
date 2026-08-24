@extends('layouts.user')

@section('title', 'Announcements')

@section('content')
    <div class="row g-3">
        @forelse ($announcements as $announcement)
            <div class="col-md-6">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">{{ $announcement->title }}</h6>
                        <span class="badge bg-secondary">{{ str_replace('_', ' ', $announcement->category) }}</span>
                    </div>
                    <p class="text-muted small mb-2">
                        {{ $announcement->creator->name }} &middot; {{ $announcement->published_at?->diffForHumans() }}
                    </p>
                    <p class="mb-0">{{ $announcement->body }}</p>
                </div>
            </div>
        @empty
            <p class="text-muted">No announcements yet.</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $announcements->links() }}</div>
@endsection
