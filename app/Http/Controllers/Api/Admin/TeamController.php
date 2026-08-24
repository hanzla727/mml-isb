<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        return Team::query()
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->withCount('users')
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        return Team::create($validated);
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('teams', 'name')->where('department_id', $request->department_id)->ignore($team->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $team->update($validated);

        return $team;
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(['message' => 'Team deleted.']);
    }
}
