<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background: #1e1b3a;">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/dashboard') }}">Volunteer Mgmt</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/reports*') ? 'active' : '' }}" href="{{ url('/dashboard/reports') }}">My Reports</a></li>
                @php
                    $unreadFieldVisits = auth()->user()->meetingsParticipating()->wherePivotNull('read_at')->count();
                    $unreadSchedule = auth()->user()->scheduledMeetingsParticipating()->wherePivotNull('read_at')->count();
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('dashboard/meetings*') ? 'active' : '' }}" href="{{ url('/dashboard/meetings') }}">
                        Field Visits
                        @if ($unreadFieldVisits > 0)
                            <span class="badge bg-danger rounded-pill">{{ $unreadFieldVisits }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('dashboard/schedule*') ? 'active' : '' }}" href="{{ url('/dashboard/schedule') }}">
                        Meetings
                        @if ($unreadSchedule > 0)
                            <span class="badge bg-danger rounded-pill">{{ $unreadSchedule }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/tasks*') ? 'active' : '' }}" href="{{ url('/dashboard/tasks') }}">My Tasks</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/targets*') ? 'active' : '' }}" href="{{ url('/dashboard/targets') }}">My Targets</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/announcements*') ? 'active' : '' }}" href="{{ url('/dashboard/announcements') }}">Announcements</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/progress*') ? 'active' : '' }}" href="{{ url('/dashboard/progress') }}">My Progress</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/performance*') ? 'active' : '' }}" href="{{ url('/dashboard/performance') }}">My Performance</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/leave-requests*') ? 'active' : '' }}" href="{{ url('/dashboard/leave-requests') }}">My Leave</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/expense-claims*') ? 'active' : '' }}" href="{{ url('/dashboard/expense-claims') }}">My Expenses</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard/documents*') ? 'active' : '' }}" href="{{ url('/dashboard/documents') }}">My Documents</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('guide') ? 'active' : '' }}" href="{{ route('guide') }}"><i class="bi bi-life-preserver"></i> How It Works</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ auth()->user()->name }}</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Log Out</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">@yield('title', 'Dashboard')</h3>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
