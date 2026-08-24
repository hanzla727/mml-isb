@extends('layouts.guest')

@section('title', 'Reset your password')

@section('content')
    <p class="small text-muted">Enter your email and we'll send you a password reset link.</p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small text-muted">Back to sign in</a>
        </div>
    </form>
@endsection
