<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Team;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;

/**
 * Lightweight read-only lookups for the mobile app's "who is this for"
 * audience pickers (meeting/task creation) — Department is global so every
 * authenticated user sees the full list, but Team is scoped through
 * HierarchyScope the same way everything else in the app is, so a Team
 * Leader/NA Head/UC Head only ever sees teams within their own reach.
 */
class OrgDirectoryController extends Controller
{
    public function departments()
    {
        return response()->json(['data' => Department::orderBy('name')->get(['id', 'name'])]);
    }

    public function teams(Request $request)
    {
        $query = Team::query()->where('is_active', true)->with('uc');
        HierarchyScope::restrictByUc($query, $request->user());

        $teams = $query->orderBy('name')->get()->map(fn (Team $team) => [
            'id' => $team->id,
            'name' => $team->name,
            'uc_name' => $team->uc?->name,
        ]);

        return response()->json(['data' => $teams]);
    }
}
