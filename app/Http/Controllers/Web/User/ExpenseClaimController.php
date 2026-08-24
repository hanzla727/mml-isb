<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\ExpenseClaim;
use App\Notifications\ExpenseClaimSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ExpenseClaimController extends Controller
{
    public function index(Request $request)
    {
        $expenseClaims = $request->user()->expenseClaims()->orderByDesc('created_at')->paginate(15);

        return view('user.expense-claims.index', ['expenseClaims' => $expenseClaims]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_type' => ['required', Rule::in(['travel', 'supplies', 'food', 'accommodation', 'other'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $expenseClaim = $request->user()->expenseClaims()->create($validated);

        $reviewers = ExpenseClaim::reviewersFor($request->user(), 'manage-expense-claims');
        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new ExpenseClaimSubmittedNotification($expenseClaim));
        }

        return back()->with('status', 'Expense claim submitted.');
    }
}
