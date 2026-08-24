<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function myMeetings(Request $request)
    {
        $user = $request->user();

        $meetings = Meeting::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->with(['contact', 'dailyReport.user', 'participants'])
            ->orderByDesc('meeting_datetime')
            ->paginate($request->integer('per_page', 20));

        return MeetingResource::collection($meetings);
    }

    public function show(Request $request, Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        return new MeetingResource($meeting->load(['contact', 'dailyReport.user', 'participants']));
    }

    public function markRead(Request $request, Meeting $meeting)
    {
        $user = $request->user();

        abort_unless($meeting->isParticipant($user), 403, 'You are not a participant in this meeting.');

        $meeting->participants()->updateExistingPivot($user->id, ['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }
}
