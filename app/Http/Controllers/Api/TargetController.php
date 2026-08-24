<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTargetRequest;
use App\Http\Resources\TargetResource;
use App\Models\Target;
use App\Services\TargetProgressUpdater;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index(Request $request, TargetProgressUpdater $progressUpdater)
    {
        $user = $request->user();

        if ($user->can('manage-targets') || $user->can('view-targets')) {
            $targets = Target::query()
                ->when($request->filled('user_id'), fn ($q) => $q->where('created_by', $request->integer('user_id')))
                ->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 20));

            return TargetResource::collection($targets);
        }

        $targets = Target::query()->where('is_active', true)->applicableTo($user)->get();

        $progressUpdater->attachCurrentProgress($targets, $user);

        return TargetResource::collection($targets);
    }

    public function store(StoreTargetRequest $request)
    {
        $target = Target::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);

        return new TargetResource($target);
    }

    public function update(StoreTargetRequest $request, Target $target)
    {
        $target->update($request->validated());

        return new TargetResource($target);
    }

    public function destroy(Target $target)
    {
        $target->delete();

        return response()->json(['message' => 'Target deleted.']);
    }
}
