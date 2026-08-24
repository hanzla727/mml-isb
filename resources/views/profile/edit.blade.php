@extends('layouts.' . $layout)

@section('title', 'Profile')

@section('content')
    <div class="card stat-card p-4" style="max-width: 600px;">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="form-control" required>
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-control" required>
                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Avatar</label>
                <input type="file" name="avatar" class="form-control" accept="image/*">
                @error('avatar') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection
