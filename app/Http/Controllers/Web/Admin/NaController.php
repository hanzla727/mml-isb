<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\DailyReport;
use App\Models\Na;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\HierarchyScope;
use App\Services\NaPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class NaController extends Controller
{
    public function index(Request $request, NaPerformanceService $performance)
    {
        $query = Na::with('naHead')->withCount(['ucs', 'members']);
        HierarchyScope::restrictByNa($query, $request->user(), 'id');

        $nas = $query->orderBy('name')->get()->map(function (Na $na) use ($performance) {
            $na->setAttribute('score', $performance->score($na));

            return $na;
        });

        return view('admin.nas.index', [
            'nas' => $nas,
            'potentialHeads' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, Na $na, NaPerformanceService $performance)
    {
        $this->authorizeVisible($request, $na);

        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : Carbon::today()->subDays(30);
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : Carbon::today();

        $memberIds = User::where('na_id', $na->id)->pluck('id');

        return view('admin.nas.show', [
            'na' => $na->load(['naHead', 'ucs.teams.department']),
            'summary' => $performance->summarize($na, $from, $to),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'recentMeetings' => ScheduledMeeting::whereHas('participants', fn ($q) => $q->whereIn('users.id', $memberIds))
                ->where('meeting_date', '<', Carbon::today())
                ->orderByDesc('meeting_date')->limit(5)->get(),
            'upcomingMeetings' => ScheduledMeeting::whereHas('participants', fn ($q) => $q->whereIn('users.id', $memberIds))
                ->where('meeting_date', '>=', Carbon::today())
                ->orderBy('meeting_date')->limit(5)->get(),
            'pendingTasks' => Task::whereHas('assignees', fn ($q) => $q->whereIn('users.id', $memberIds))
                ->whereIn('status', Task::OPEN_STATUSES)
                ->orderByDesc('created_at')->limit(5)->get(),
            'pendingReports' => DailyReport::whereIn('user_id', $memberIds)
                ->whereIn('review_status', ['pending_review', 'under_review', 're_submitted'])
                ->with('user')->orderByDesc('report_date')->limit(5)->get(),
            'announcements' => Announcement::where(function ($q) use ($na) {
                $ucIds = $na->ucs()->pluck('id');
                $teamIds = Team::whereIn('uc_id', $ucIds)->pluck('id');
                $departmentIds = Team::whereIn('uc_id', $ucIds)->pluck('department_id')->unique();

                $q->where('audience_scope', 'all')
                    ->orWhere(fn ($q2) => $q2->where('audience_scope', 'na')->where('audience_id', $na->id))
                    ->orWhere(fn ($q2) => $q2->where('audience_scope', 'uc')->whereIn('audience_id', $ucIds))
                    ->orWhere(fn ($q2) => $q2->where('audience_scope', 'department')->whereIn('audience_id', $departmentIds))
                    ->orWhere(fn ($q2) => $q2->where('audience_scope', 'team')->whereIn('audience_id', $teamIds));
            })->orderByDesc('published_at')->limit(5)->get(),
        ]);
    }

    public function compare(Request $request, NaPerformanceService $performance)
    {
        $period = $request->string('period', 'month')->toString();
        [$from, $to] = $performance->periodBounds($period);

        $query = Na::query();
        HierarchyScope::restrictByNa($query, $request->user(), 'id');

        $nas = $query->orderBy('name')->get();
        $comparison = $performance->compare($nas, $from, $to);

        return view('admin.nas.compare', [
            'period' => $period,
            'comparison' => $comparison,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        Na::create($validated);

        return redirect()->route('admin.nas.index')->with('status', 'NA created.');
    }

    public function update(Request $request, Na $na)
    {
        $validated = $this->validated($request);

        $na->update($validated);

        // Keep the na_head role assignment consistent with whoever is now
        // pointed to by na_head_id.
        if (! empty($validated['na_head_id'])) {
            $head = User::find($validated['na_head_id']);
            if ($head && ! $head->hasRole('na_head')) {
                $head->assignRole('na_head');
            }
            $head?->update(['na_id' => $na->id]);
        }

        return back()->with('status', 'NA updated.');
    }

    public function destroy(Na $na)
    {
        $na->delete();

        return redirect()->route('admin.nas.index')->with('status', 'NA deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'na_head_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function authorizeVisible(Request $request, Na $na): void
    {
        $naIds = HierarchyScope::visibleNaIds($request->user());

        abort_unless($naIds === null || in_array($na->id, $naIds, true), 403);
    }
}
