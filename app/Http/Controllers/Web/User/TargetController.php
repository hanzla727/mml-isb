<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Services\TargetProgressUpdater;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index(Request $request, TargetProgressUpdater $progressUpdater)
    {
        $user = $request->user();

        $targets = Target::query()->where('is_active', true)->applicableTo($user)->get();

        $progressUpdater->attachCurrentProgress($targets, $user);

        return view('user.targets.index', ['targets' => $targets]);
    }
}
