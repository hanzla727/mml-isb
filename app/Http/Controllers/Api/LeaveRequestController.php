<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Notifications\LeaveRequestDecidedNotification;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $leaveRequests = $request->user()->leaveRequests()
            ->with('reviewer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $leaveRequest = $request->user()->leaveRequests()->create($request->validated());

        return new LeaveRequestResource($leaveRequest);
    }

    /**
     * The review queue for Team Leaders/NA Heads/Admins — scoped to the
     * requests of volunteers they're allowed to see (App\Services\HierarchyScope),
     * distinct from index() above which is always "my own requests".
     */
    public function adminIndex(Request $request)
    {
        $query = LeaveRequest::query()->with(['user', 'reviewer']);
        HierarchyScope::restrictByOwner($query, $request->user());

        $leaveRequests = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return LeaveRequestResource::collection($leaveRequests);
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

        return new LeaveRequestResource($leaveRequest->fresh(['user', 'reviewer']));
    }
}
