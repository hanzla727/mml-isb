<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $meetings = Meeting::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->with(['contact', 'dailyReport.user'])
            ->with(['participants' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('meeting_datetime')
            ->paginate(15);

        return view('user.meetings.index', ['meetings' => $meetings]);
    }

    public function show(Request $request, Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        $user = $request->user();

        if ($meeting->isParticipant($user)) {
            $meeting->participants()->updateExistingPivot($user->id, ['read_at' => now()]);
        }

        return view('user.meetings.show', [
            'meeting' => $meeting->load(['contact', 'dailyReport.user', 'participants']),
        ]);
    }
}
