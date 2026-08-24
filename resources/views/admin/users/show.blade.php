@extends('layouts.admin')

@section('title', $viewedUser->name)

@section('content')
    <div class="card stat-card p-4 mb-3">
        <h5>{{ $viewedUser->name }}</h5>
        <p class="text-muted small mb-2">{{ $viewedUser->email }} &middot; {{ $viewedUser->phone ?? '—' }}</p>
        <div class="mb-2"><strong>Department:</strong> {{ $viewedUser->department?->name ?? '—' }}</div>
        <div class="mb-2"><strong>Team:</strong> {{ $viewedUser->team?->name ?? '—' }}</div>
        <a href="{{ route('admin.performance.show', $viewedUser) }}" class="btn btn-sm btn-outline-primary">View Performance</a>
    </div>

    <div class="card stat-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Documents</h6>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Document</button>
        </div>

        <table class="table table-hover mb-0">
            <thead><tr><th>Title</th><th>Type</th><th>Uploaded By</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>{{ $document->title }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($document->document_type) }}</span></td>
                        <td>{{ $document->uploader->name }}</td>
                        <td class="text-muted small">{{ $document->created_at->toDateString() }}</td>
                        <td class="d-flex gap-2">
                            @if ($document->file)
                                <a href="{{ asset('storage/' . $document->file->path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                            @endif
                            <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" onsubmit="return confirm('Delete this document?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="uploadModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.documents.store', $viewedUser) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Upload Document</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="document_type" class="form-select" required>
                                <option value="cnic">CNIC</option>
                                <option value="certificate">Certificate</option>
                                <option value="agreement">Agreement</option>
                                <option value="training">Training</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
