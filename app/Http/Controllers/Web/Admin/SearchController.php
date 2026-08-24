<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\DailyReport;
use App\Models\Na;
use App\Models\Project;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\Uc;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->string('q'));
        $user = $request->user();

        if ($query === '') {
            return view('admin.search.index', [
                'query' => $query,
                'contacts' => collect(), 'tasks' => collect(), 'reports' => collect(),
                'meetings' => collect(), 'projects' => collect(), 'nas' => collect(), 'ucs' => collect(),
            ]);
        }

        $contacts = Contact::query()
            ->when(true, fn ($q) => HierarchyScope::restrictByOwner($q, $user, 'created_by'))
            ->where(fn ($q) => $q->where('name', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('cnic', 'like', "%{$query}%"))
            ->limit(20)->get();

        $tasksQuery = Task::query()->where('title', 'like', "%{$query}%");
        HierarchyScope::restrictByRelation($tasksQuery, $user, 'assignees');
        $tasks = $tasksQuery->with('assignees')->limit(20)->get();

        $reportsQuery = DailyReport::query()
            ->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$query}%"));
        HierarchyScope::restrictByOwner($reportsQuery, $user);
        $reports = $reportsQuery->with('user')->limit(20)->get();

        $meetingsQuery = ScheduledMeeting::query()->where('title', 'like', "%{$query}%");
        HierarchyScope::restrictByRelation($meetingsQuery, $user, 'participants');
        $meetings = $meetingsQuery->limit(20)->get();

        $ucIds = HierarchyScope::visibleUcIds($user);

        $projectsQuery = Project::query()->where('name', 'like', "%{$query}%");
        if ($ucIds !== null) {
            $projectsQuery->whereIn('uc_id', $ucIds);
        }
        $projects = $projectsQuery->limit(20)->get();

        $ucsQuery = Uc::query()->where(fn ($q) => $q->where('name', 'like', "%{$query}%")->orWhere('sector', 'like', "%{$query}%"));
        if ($ucIds !== null) {
            $ucsQuery->whereIn('id', $ucIds);
        }
        $ucs = $ucsQuery->limit(20)->get();

        $naIds = HierarchyScope::visibleNaIds($user);

        $nasQuery = Na::query()->where('name', 'like', "%{$query}%");
        if ($naIds !== null) {
            $nasQuery->whereIn('id', $naIds);
        }
        $nas = $nasQuery->limit(20)->get();

        return view('admin.search.index', compact('query', 'contacts', 'tasks', 'reports', 'meetings', 'projects', 'nas', 'ucs'));
    }
}
