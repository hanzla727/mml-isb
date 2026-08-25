<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduledMeetingRequest;
use App\Http\Resources\ScheduledMeetingResource;
use App\Models\ScheduledMeeting;
use App\Services\HierarchyScope;
use App\Services\ScheduledMeetingService;
use Illuminate\Http\Request;

class ScheduledMeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ScheduledMeeting::query()->with(['organizer'])->withCount(['participants', 'tasks']);

        // Visibility follows the org hierarchy (Admin/NA Head/Team Leader see
        // their scope's meetings, not just their own), independent of
        // 'manage-meetings' which gates create/edit/delete rights instead.
        if ($user->hasAnyRole(['super_admin', 'admin', 'na_head', 'uc_head', 'team_leader'])) {
            HierarchyScope::restrictByRelation($query, $user, 'participants');
            $query->with(['participants' => fn ($q) => $q->where('user_id', $user->id)]);

            $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        } else {
            $query->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->with(['participants' => fn ($q) => $q->where('user_id', $user->id)]);
        }

        // The Upcoming/Today/Past tabs apply to every role's list, not just
        // the self-only branch — Team Leaders/NA Heads/Admins get the same
        // date scoping over their wider set of visible meetings.
        if ($request->filled('when')) {
            match ($request->string('when')->toString()) {
                'today' => $query->whereDate('meeting_date', today()),
                'past' => $query->whereDate('meeting_date', '<', today()),
                default => $query->whereDate('meeting_date', '>=', today()),
            };
        } elseif (! $user->hasAnyRole(['super_admin', 'admin', 'na_head', 'uc_head', 'team_leader'])) {
            $query->whereDate('meeting_date', '>=', today());
        }

        $meetings = $query->orderBy('meeting_date')->orderBy('start_time')->paginate($request->integer('per_page', 20));

        return ScheduledMeetingResource::collection($meetings);
    }

    public function show(Request $request, ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('view', $scheduledMeeting);

        return new ScheduledMeetingResource(
            $scheduledMeeting->load(['organizer', 'tasks', 'participants' => fn ($q) => $q->where('user_id', $request->user()->id)])
        );
    }

    public function store(StoreScheduledMeetingRequest $request, ScheduledMeetingService $service)
    {
        $this->authorize('create', ScheduledMeeting::class);

        $meeting = $service->create($request->user(), $request->validated());

        return new ScheduledMeetingResource($meeting->load('organizer'));
    }

    public function update(StoreScheduledMeetingRequest $request, ScheduledMeeting $scheduledMeeting, ScheduledMeetingService $service)
    {
        $this->authorize('update', $scheduledMeeting);

        $meeting = $service->update($request->user(), $scheduledMeeting, $request->validated());

        return new ScheduledMeetingResource($meeting->load('organizer'));
    }

    public function destroy(ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('delete', $scheduledMeeting);

        $scheduledMeeting->delete();

        return response()->json(['message' => 'Meeting cancelled.']);
    }
}
