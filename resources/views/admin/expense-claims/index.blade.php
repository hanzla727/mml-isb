@extends('layouts.admin')

@section('title', 'Expense Claims')

@section('content')
    <div class="card stat-card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['pending', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Volunteer</th><th>Type</th><th>Amount</th><th>Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($expenseClaims as $expenseClaim)
                    <tr>
                        <td>{{ $expenseClaim->user->name }}</td>
                        <td>{{ ucfirst($expenseClaim->expense_type) }}</td>
                        <td>{{ number_format($expenseClaim->amount, 2) }}</td>
                        <td>{{ $expenseClaim->date->toDateString() }}</td>
                        <td>
                            <span class="badge bg-{{ $expenseClaim->status === 'approved' ? 'success' : ($expenseClaim->status === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($expenseClaim->status) }}
                            </span>
                        </td>
                        <td>
                            @if ($expenseClaim->status === 'pending')
                                <form method="POST" action="{{ route('admin.expense-claims.review', $expenseClaim) }}" class="d-flex gap-2">
                                    @csrf @method('PUT')
                                    <button name="decision" value="approve" class="btn btn-sm btn-outline-success">Approve</button>
                                    <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @else
                                <span class="text-muted small">by {{ $expenseClaim->reviewer?->name ?? '—' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No expense claims found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $expenseClaims->links() }}</div>
@endsection
