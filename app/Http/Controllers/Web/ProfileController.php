<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $layout = $request->user()->hasAnyRole(['super_admin', 'admin']) ? 'admin' : 'user';

        return view('profile.edit', ['layout' => $layout]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', File::image()->max(5 * 1024)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        unset($validated['avatar']);

        $user->update($validated);

        return back()->with('status', 'Profile updated.');
    }
}
