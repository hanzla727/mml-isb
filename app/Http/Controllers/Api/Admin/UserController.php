<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['department', 'team', 'roles']);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->string('search');
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        })
            ->when($request->filled('role'), fn ($q) => $q->role($request->string('role')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->when($request->filled('team_id'), fn ($q) => $q->where('team_id', $request->integer('team_id')));

        $users = $query->orderBy('name')->paginate($request->integer('per_page', 20));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'pin' => $validated['pin'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->assignRole($validated['role']);

        return new UserResource($user->load(['department', 'team', 'roles']));
    }

    public function show(User $user)
    {
        return new UserResource($user->load(['department', 'team', 'roles']));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
            'is_active' => $validated['is_active'] ?? $user->is_active,
            'pin' => $validated['pin'] ?? null,
            ...(! empty($validated['password']) ? ['password' => $validated['password']] : []),
        ]);

        $user->syncRoles([$validated['role']]);

        return new UserResource($user->load(['department', 'team', 'roles']));
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->id === $user->id, 422, 'You cannot delete your own account.');

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    public function resetPassword(User $user)
    {
        $temporaryPassword = Str::password(12);

        $user->forceFill(['password' => Hash::make($temporaryPassword)])->save();
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset.', 'temporary_password' => $temporaryPassword]);
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            $user->tokens()->delete();
        }

        return new UserResource($user);
    }
}
