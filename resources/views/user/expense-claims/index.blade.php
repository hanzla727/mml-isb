@extends('layouts.user')

@section('title', 'My Expense Claims')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">New Claim</button>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>Type</th><th>Amount</th><th>Date</th><th>Description</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($expenseClaims as $expenseClaim)
                    <tr>
                        <td>{{ ucfirst($expenseClaim->expense_type) }}</td>
                        <td>{{ number_format($expenseClaim->amount, 2) }}</td>
                        <td>{{ $expenseClaim->date->toDateString() }}</td>
                        <td>{{ $expenseClaim->description ?: '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $expenseClaim->status === 'approved' ? 'success' : ($expenseClaim->status === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($expenseClaim->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No expense claims yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $expenseClaims->links() }}</div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('user.expense-claims.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">New Expense Claim</h5></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="expense_type" class="form-select" required>
                                <option value="travel">Travel</option>
                                <option value="supplies">Supplies</option>
                                <option value="food">Food</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
