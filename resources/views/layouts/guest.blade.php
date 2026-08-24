<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: linear-gradient(135deg, #4f46e5, #1e1b3a);">
<div class="card shadow-lg border-0" style="width: 100%; max-width: 400px; border-radius: 1rem;">
    <div class="card-body p-4">
        <h4 class="text-center mb-1 fw-semibold">{{ config('app.name') }}</h4>
        <p class="text-center text-muted mb-4">@yield('title', 'Sign in to your account')</p>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success py-2 small">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</div>
</body>
</html>
