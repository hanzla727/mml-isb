<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Notifications\LeaveRequestDecidedNotification;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::query()->with(['user', 'reviewer']);
        HierarchyScope::restrictByOwner($query, $request->user());

        $leaveRequests = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.leave-requests.index', ['leaveRequests' => $leaveRequests]);
    }

    public function show(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless(HierarchyScope::canView($request->user(), $leaveRequest->user), 403);

        return view('admin.leave-requests.show', [
            'leaveRequest' => $leaveRequest->load(['user', 'reviewer']),
        ]);
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless(HierarchyScope::canView($request->user(), $leaveRequest->user), 403);
        abort_unless($leaveRequest->status === 'pending', 422, 'This request has already been decided.');

        $validated = $request->validate(['decision' => ['required', Rule::in(['approve', 'reject'])]]);

        $validated['decision'] === 'approve'
            ? $leaveRequest->approve($request->user())
            : $leaveRequest->reject($request->user());

        $leaveRequest->user->notify(new LeaveRequestDecidedNotification($leaveRequest));

        return back()->with('status', 'Leave request updated.');
    }
}
