<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseClaim;
use App\Notifications\ExpenseClaimDecidedNotification;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseClaimController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseClaim::query()->with(['user', 'reviewer']);
        HierarchyScope::restrictByOwner($query, $request->user());

        $expenseClaims = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.expense-claims.index', ['expenseClaims' => $expenseClaims]);
    }

    public function review(Request $request, ExpenseClaim $expenseClaim)
    {
        abort_unless(HierarchyScope::canView($request->user(), $expenseClaim->user), 403);
        abort_unless($expenseClaim->status === 'pending', 422, 'This claim has already been decided.');

        $validated = $request->validate(['decision' => ['required', Rule::in(['approve', 'reject'])]]);

        $validated['decision'] === 'approve'
            ? $expenseClaim->approve($request->user())
            : $expenseClaim->reject($request->user());

        $expenseClaim->user->notify(new ExpenseClaimDecidedNotification($expenseClaim));

        return back()->with('status', 'Expense claim updated.');
    }
}
