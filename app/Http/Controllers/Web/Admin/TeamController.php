<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Team;
use App\Models\Uc;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        return view('admin.teams.index', [
            'teams' => Team::with(['department', 'uc'])->withCount('users')->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'ucs' => Uc::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'uc_id' => ['required', 'exists:ucs,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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
