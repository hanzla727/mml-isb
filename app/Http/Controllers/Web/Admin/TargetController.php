<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTargetRequest;
use App\Models\Department;
use App\Models\Target;
use App\Models\Team;
use App\Models\User;

class TargetController extends Controller
{
    public function index()
    {
        return view('admin.targets.index', [
            'targets' => Target::with('creator')->orderByDesc('created_at')->paginate(20),
            'departments' => Department::orderBy('name')->get(),
            'teams' => Team::orderBy('name')->get(),
            'users' => User::role('user')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTargetRequest $request)
    {
        Target::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Target created.');
    }

    public function destroy(Target $target)
    {
        $target->delete();

        return back()->with('status', 'Target deleted.');
    }
}
