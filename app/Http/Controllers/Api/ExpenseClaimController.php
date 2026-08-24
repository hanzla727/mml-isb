<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseClaimRequest;
use App\Http\Resources\ExpenseClaimResource;
use App\Models\Media;
use Illuminate\Http\Request;

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
}
