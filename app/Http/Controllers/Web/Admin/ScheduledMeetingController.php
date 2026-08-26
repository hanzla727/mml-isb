<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduledMeetingRequest;
use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Na;
use App\Models\Project;
use App\Models\ScheduledMeeting;
use App\Models\Uc;
use App\Models\User;
use App\Services\HierarchyScope;
use App\Services\ScheduledMeetingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduledMeetingController extends Controller
{
    public function index()
    {
        return view('admin.meetings.index');
    }

    public function show(Request $request, ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('view', $scheduledMeeting);

        $naIds = HierarchyScope::visibleNaIds($request->user());
        $ucIds = HierarchyScope::visibleUcIds($request->user());

        return view('admin.meetings.show', [
            'meeting' => $scheduledMeeting->load(['organizer', 'project', 'participants', 'tasks.assignees', 'tasks.latestReport', 'attendances']),
            'nas' => Na::when($naIds !== null, fn ($q) => $q->whereIn('id', $naIds))->orderBy('name')->get(),
            'ucs' => Uc::when($ucIds !== null, fn ($q) => $q->whereIn('id', $ucIds))->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'formTemplates' => FormTemplate::orderBy('name')->get(),
        ]);
    }

    public function updateStatus(Request $request, ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('update', $scheduledMeeting);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['upcoming', 'ongoing', 'completed', 'cancelled'])],
        ]);

        $scheduledMeeting->update($validated);

        return back()->with('status', 'Meeting status updated.');
    }

    public function markAttendance(Request $request, ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('update', $scheduledMeeting);

        $data = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', Rule::in(['present', 'late', 'absent', 'excused'])],
        ]);

        foreach ($data['attendance'] as $userId => $status) {
            $scheduledMeeting->attendances()->updateOrCreate(
                ['user_id' => $userId],
                ['status' => $status, 'marked_by' => $request->user()->id, 'marked_at' => now()]
            );
        }

        return back()->with('status', 'Attendance saved.');
    }

    public function store(StoreScheduledMeetingRequest $request, ScheduledMeetingService $service)
    {
        $this->authorize('create', ScheduledMeeting::class);

        $meeting = $service->create($request->user(), $request->validated());

        return redirect()->route('admin.meetings.show', $meeting)->with('status', 'Meeting created.');
    }

    public function update(StoreScheduledMeetingRequest $request, ScheduledMeeting $scheduledMeeting, ScheduledMeetingService $service)
    {
        $this->authorize('update', $scheduledMeeting);

        $service->update($request->user(), $scheduledMeeting, $request->validated());

        return back()->with('status', 'Meeting updated.');
    }

    public function destroy(ScheduledMeeting $scheduledMeeting)
    {
        $this->authorize('delete', $scheduledMeeting);

        $scheduledMeeting->delete();

        return redirect()->route('admin.meetings.index')->with('status', 'Meeting cancelled.');
    }
}
