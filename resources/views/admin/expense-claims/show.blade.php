@extends('layouts.admin')

@section('title', 'Expense Claim')

@section('content')
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card stat-card p-3">
                <h6 class="mb-2">Receipt</h6>
                @if ($expenseClaim->receipt)
                    <a href="{{ asset('storage/'.$expenseClaim->receipt->path) }}" target="_blank">
                        <img src="{{ asset('storage/'.$expenseClaim->receipt->path) }}" alt="Receipt" class="img-fluid rounded border">
                    </a>
                @else
                    <p class="text-muted">No receipt attached.</p>
                @endif
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card stat-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ ucfirst($expenseClaim->expense_type) }}</h5>
                        <p class="text-muted small mb-0">{{ $expenseClaim->user->name }} &middot; {{ $expenseClaim->date->toDateString() }}</p>
                    </div>
                    <span class="badge bg-{{ $expenseClaim->status === 'approved' ? 'success' : ($expenseClaim->status === 'rejected' ? 'danger' : 'secondary') }} fs-6">
                        {{ ucfirst($expenseClaim->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Amount</div>
                    <div class="fs-4 fw-semibold">{{ number_format($expenseClaim->amount, 2) }}</div>
                </div>

                @if ($expenseClaim->description)
                    <div class="mb-3">
                        <div class="text-muted small">Description</div>
                        <div>{{ $expenseClaim->description }}</div>
                    </div>
                @endif

                @if ($expenseClaim->status !== 'pending')
                    <div class="mb-3">
                        <div class="text-muted small">Reviewed by</div>
                        <div>{{ $expenseClaim->reviewer?->name ?? '—' }} &middot; {{ $expenseClaim->reviewed_at?->diffForHumans() }}</div>
                    </div>
                @endif

                @if ($expenseClaim->status === 'pending')
                    <form method="POST" action="{{ route('admin.expense-claims.review', $expenseClaim) }}" class="d-flex gap-2 mt-3">
                        @csrf @method('PUT')
                        <button name="decision" value="approve" class="btn btn-success">Approve</button>
                        <button name="decision" value="reject" class="btn btn-outline-danger">Reject</button>
                    </form>
                @endif

                <a href="{{ route('admin.expense-claims.index') }}" class="btn btn-link ps-0 mt-2">&larr; Back to Expense Claims</a>
            </div>
        </div>
    </div>
@endsection
