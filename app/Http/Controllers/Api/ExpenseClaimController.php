<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseClaimRequest;
use App\Http\Resources\ExpenseClaimResource;
use App\Models\ExpenseClaim;
use App\Models\Media;
use App\Notifications\ExpenseClaimDecidedNotification;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseClaimController extends Controller
{
    public function index(Request $request)
    {
        $expenseClaims = $request->user()->expenseClaims()
            ->with(['reviewer', 'receipt'])
            ->orderByDesc('created_at')
            ->get();

        return ExpenseClaimResource::collection($expenseClaims);
    }

    public function store(StoreExpenseClaimRequest $request)
    {
        $validated = $request->safe()->except('receipt');

        $expenseClaim = $request->user()->expenseClaims()->create($validated);

        $path = $request->file('receipt')->store('expense-receipts', 'public');
        $expenseClaim->receipt()->save(new Media([
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $request->file('receipt')->getClientMimeType(),
            'size' => $request->file('receipt')->getSize(),
        ]));

        return new ExpenseClaimResource($expenseClaim->load('receipt'));
    }

    /**
     * The review queue for Team Leaders/NA Heads/Admins — scoped via
     * App\Services\HierarchyScope, distinct from index() ("my own claims").
     */
    public function adminIndex(Request $request)
    {
        $query = ExpenseClaim::query()->with(['user', 'reviewer', 'receipt']);
        HierarchyScope::restrictByOwner($query, $request->user());

        $expenseClaims = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ExpenseClaimResource::collection($expenseClaims);
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

        return new ExpenseClaimResource($expenseClaim->fresh(['user', 'reviewer', 'receipt']));
    }
}
