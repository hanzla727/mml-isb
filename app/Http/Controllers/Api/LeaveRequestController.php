<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $leaveRequests = $request->user()->leaveRequests()
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $leaveRequest = $request->user()->leaveRequests()->create($request->validated());

        return new LeaveRequestResource($leaveRequest);
    }
}
