<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HierarchyScope;
use Illuminate\Http\Request;

class MyTeamController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['department', 'team', 'roles']);
        HierarchyScope::restrictUsersQuery($query, $request->user());

        $members = $query->where('id', '!=', $request->user()->id)->orderBy('name')->get();

        return view('admin.my-team.index', [
            'teams' => $request->user()->teamsLed,
            'members' => $members,
        ]);
    }
}
