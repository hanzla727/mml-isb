@extends('layouts.admin')

@section('title', $viewedUser->name)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            @if ($viewedUser->avatar_path)
                <img src="{{ asset('storage/'.$viewedUser->avatar_path) }}" alt="" class="rounded-circle" width="64" height="64" style="object-fit: cover;">
            @else
                <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-secondary fw-semibold" style="width:64px; height:64px; font-size:24px;">
                    {{ strtoupper(substr($viewedUser->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h5 class="mb-0">{{ $viewedUser->name }}</h5>
                <p class="text-muted small mb-0">{{ $viewedUser->email }} &middot; {{ $viewedUser->phone ?? '—' }}</p>
            </div>
        </div>
        <div class="mb-2"><strong>{{ __('Department') }}:</strong> {{ $viewedUser->department?->name ?? '—' }}</div>

        <div class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-at text-muted"></i>
            <span class="text-muted">{{ __('Username') }}</span>
            <span class="ms-auto">{{ $viewedUser->username ?: '--' }}</span>
        </div>
        <div class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-key text-muted"></i>
            <span class="text-muted">{{ __('Pin') }}</span>
            <span class="ms-auto font-monospace" id="pinValue" data-pin="{{ $viewedUser->pin }}" data-masked="1">
                {{ $viewedUser->pin ? '****' : '--' }}
            </span>
            @if ($viewedUser->pin)
                <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="togglePinVisibility()" title="{{ __('Show/Hide PIN') }}">
                    <i class="bi bi-eye" id="pinEyeIcon"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="copyPin()" title="{{ __('Copy PIN') }}">
                    <i class="bi bi-clipboard"></i>
                </button>
            @endif
        </div>
        <a href="{{ route('admin.performance.show', $viewedUser) }}" class="btn btn-sm btn-outline-primary">{{ __('View Performance') }}</a>
    </div>

    <div class="card stat-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ __('Documents') }}</h6>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">{{ __('Upload Document') }}</button>
        </div>

        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Uploaded By') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>{{ $document->title }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($document->document_type) }}</span></td>
                        <td>{{ $document->uploader->name }}</td>
                        <td class="text-muted small">{{ $document->created_at->toDateString() }}</td>
                        <td class="d-flex gap-2">
                            @if ($document->file)
                                <a href="{{ asset('storage/' . $document->file->path) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                            @endif
                            <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" onsubmit="return confirm('{{ __('Delete this document?') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No documents uploaded yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="uploadModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.documents.store', $viewedUser) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">{{ __('Upload Document') }}</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Title') }}</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Type') }}</label>
                            <select name="document_type" class="form-select" required>
                                <option value="cnic">{{ __('CNIC') }}</option>
                                <option value="certificate">{{ __('Certificate') }}</option>
                                <option value="agreement">{{ __('Agreement') }}</option>
                                <option value="training">{{ __('Training') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('File') }}</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePinVisibility() {
            const el = document.getElementById('pinValue');
            const icon = document.getElementById('pinEyeIcon');
            const masked = el.dataset.masked === '1';
            el.textContent = masked ? el.dataset.pin : '****';
            el.dataset.masked = masked ? '0' : '1';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        function copyPin() {
            navigator.clipboard.writeText(document.getElementById('pinValue').dataset.pin);
        }
    </script>
@endsection
