@extends('layouts.admin')

@section('title', __('Expense Claims'))

@section('content')
    <div class="card stat-card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">{{ __('Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['pending', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100">{{ __('Filter') }}</button>
            </div>
        </form>
    </div>

    <div class="card stat-card">
        <table class="table table-hover mb-0">
            <thead><tr><th>{{ __('Volunteer') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Date') }}</th><th>{{ __('Receipt') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($expenseClaims as $expenseClaim)
                    <tr>
                        <td>{{ $expenseClaim->user->name }}</td>
                        <td>{{ ucfirst($expenseClaim->expense_type) }}</td>
                        <td>{{ number_format($expenseClaim->amount, 2) }}</td>
                        <td>{{ $expenseClaim->date->toDateString() }}</td>
                        <td>
                            @if ($expenseClaim->receipt)
                                <a href="{{ route('admin.expense-claims.show', $expenseClaim) }}">
                                    <img src="{{ asset('storage/'.$expenseClaim->receipt->path) }}" alt="{{ __('Receipt') }}" width="36" height="36" class="rounded" style="object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $expenseClaim->status === 'approved' ? 'success' : ($expenseClaim->status === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ __(ucfirst($expenseClaim->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('admin.expense-claims.show', $expenseClaim) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                                @if ($expenseClaim->status === 'pending')
                                    <form method="POST" action="{{ route('admin.expense-claims.review', $expenseClaim) }}" class="d-flex gap-2">
                                        @csrf @method('PUT')
                                        <button name="decision" value="approve" class="btn btn-sm btn-outline-success">{{ __('Approve') }}</button>
                                        <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">{{ __('Reject') }}</button>
                                    </form>
                                @else
                                    <span class="text-muted small">{{ __('by') }} {{ $expenseClaim->reviewer?->name ?? '—' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No expense claims found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $expenseClaims->links() }}</div>
@endsection
