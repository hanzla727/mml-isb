<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\ScheduledMeeting;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;

class ScheduledMeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $when = $request->string('when', 'upcoming')->toString();

        $query = ScheduledMeeting::query()->with(['organizer']);

        // Admin/NA Head/Team Leader see every meeting across their scope
        // (all participants), not just meetings they personally attend.
        if ($user->hasAnyRole(['super_admin', 'admin', 'na_head', 'uc_head', 'team_leader'])) {
            HierarchyScope::restrictByRelation($query, $user, 'participants');
            $query->with(['participants']);
        } else {
            $query->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->with(['participants' => fn ($q) => $q->where('user_id', $user->id)]);
        }

        $meetings = $query
            ->when($when === 'today', fn ($q) => $q->whereDate('meeting_date', today()))
            ->when($when === 'past', fn ($q) => $q->whereDate('meeting_date', '<', today()))
            ->when($when === 'upcoming', fn ($q) => $q->whereDate('meeting_date', '>=', today()))
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('user.schedule.index', ['meetings' => $meetings, 'when' => $when]);
    }

    public function show(Request $request, ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('view', $scheduledMeeting);

        $user = $request->user();

        if ($scheduledMeeting->isParticipant($user)) {
            $scheduledMeeting->participants()->updateExistingPivot($user->id, ['read_at' => now()]);
        }

        $scheduledMeeting->load(['organizer', 'participants', 'attendances', 'tasks' => fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $user->id))]);

        return view('user.schedule.show', [
            'meeting' => $scheduledMeeting,
            'myAttendance' => $scheduledMeeting->attendances->firstWhere('user_id', $user->id),
        ]);
    }
}
