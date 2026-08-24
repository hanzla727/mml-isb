<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Notifications\LeaveRequestSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $leaveRequests = $request->user()->leaveRequests()->orderByDesc('created_at')->paginate(15);

        return view('user.leave-requests.index', ['leaveRequests' => $leaveRequests]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => ['required', Rule::in(['sick', 'personal', 'vacation', 'emergency', 'other'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $leaveRequest = $request->user()->leaveRequests()->create($validated);

        $reviewers = LeaveRequest::reviewersFor($request->user(), 'manage-leave-requests');
        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new LeaveRequestSubmittedNotification($leaveRequest));
        }

        return back()->with('status', 'Leave request submitted.');
    }
}
