<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Team;
use App\Models\Uc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        return view('admin.teams.index', [
            'teams' => Team::with(['department', 'uc', 'leader'])->withCount('users')->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'ucs' => Uc::orderBy('name')->get(),
            // A user only shows up here once they hold the team_leader role
            // (assigned from the Users page) — this picker just says which
            // team(s) they lead; one leader can be picked for several teams.
            'teamLeaders' => User::role('team_leader')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'uc_id' => ['required', 'exists:ucs,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'leader_id' => ['nullable', 'exists:users,id'],
        ]);

        Team::create($validated);

        return back()->with('status', 'Team created.');
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'uc_id' => ['required', 'exists:ucs,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('teams', 'name')
                    ->where('department_id', $request->department_id)
                    ->where('uc_id', $request->uc_id)
                    ->ignore($team->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'leader_id' => ['nullable', 'exists:users,id'],
        ]);

        $team->update($validated);

        return back()->with('status', 'Team updated.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return back()->with('status', 'Team deleted.');
    }
}
