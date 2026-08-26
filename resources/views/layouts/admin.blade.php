<!doctype html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Admin')) - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
<div class="d-flex">
    <nav class="app-sidebar d-flex flex-column p-3" style="width: 260px; flex-shrink: 0;">
        <a href="{{ url('/admin') }}" class="sidebar-brand d-flex align-items-center gap-2 mb-4 text-decoration-none fs-5 fw-semibold">
            <i class="bi bi-heart-fill"></i> {{ __('Volunteer Mgmt') }}
        </a>
        <ul class="nav nav-pills flex-column mb-auto gap-1 overflow-y-auto">
            <li class="nav-item">
                <a href="{{ url('/admin') }}" class="nav-link {{ request()->is('admin') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/search') }}" class="nav-link {{ request()->is('admin/search*') ? 'active' : '' }}"><i class="bi bi-search"></i> {{ __('Search') }}</a>
            </li>
            @hasanyrole('super_admin|admin|na_head')
                <li class="nav-item">
                    <a href="{{ url('/admin/nas') }}" class="nav-link {{ request()->is('admin/nas*') ? 'active' : '' }}"><i class="bi bi-geo-alt"></i> {{ __('NAs') }}</a>
                </li>
                @can('manage-nas')
                    <li class="nav-item">
                        <a href="{{ url('/admin/ucs') }}" class="nav-link {{ request()->is('admin/ucs*') ? 'active' : '' }}"><i class="bi bi-pin-map"></i> {{ __('UCs') }}</a>
                    </li>
                @endcan
            @endhasanyrole

            <div class="nav-section-label">{{ __('Field Work') }}</div>
            <li class="nav-item">
                <a href="{{ url('/admin/reports') }}" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> {{ __('Reports') }}</a>
            </li>
            @can('manage-meetings')
                <li class="nav-item">
                    <a href="{{ url('/admin/meetings') }}" class="nav-link {{ request()->is('admin/meetings*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> {{ __('Meetings') }}</a>
                </li>
            @endcan
            <li class="nav-item">
                <a href="{{ url('/admin/tasks') }}" class="nav-link {{ request()->is('admin/tasks*') ? 'active' : '' }}"><i class="bi bi-list-check"></i> {{ __('Tasks') }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/contacts') }}" class="nav-link {{ request()->is('admin/contacts*') ? 'active' : '' }}"><i class="bi bi-person-lines-fill"></i> {{ __('Contacts') }}</a>
            </li>
            @can('review-task-reports')
                <li class="nav-item">
                    <a href="{{ url('/admin/task-reports') }}" class="nav-link {{ request()->is('admin/task-reports*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> {{ __('Report Reviews') }}</a>
                </li>
            @endcan
            @can('manage-projects')
                <li class="nav-item">
                    <a href="{{ url('/admin/projects') }}" class="nav-link {{ request()->is('admin/projects*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> {{ __('Projects') }}</a>
                </li>
            @endcan

            <div class="nav-section-label">{{ __('Organization') }}</div>
            @hasanyrole('super_admin|admin|na_head|uc_head')
                <li class="nav-item">
                    <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}"><i class="bi bi-people"></i> {{ __('Users') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/departments') }}" class="nav-link {{ request()->is('admin/departments*') ? 'active' : '' }}"><i class="bi bi-diagram-3"></i> {{ __('Departments') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/teams') }}" class="nav-link {{ request()->is('admin/teams*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> {{ __('Teams') }}</a>
                </li>
            @endhasanyrole
            @can('manage-team')
                <li class="nav-item">
                    <a href="{{ url('/admin/my-team') }}" class="nav-link {{ request()->is('admin/my-team*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> {{ __('My Team') }}</a>
                </li>
            @endcan
            @can('manage-leave-requests')
                <li class="nav-item">
                    <a href="{{ url('/admin/leave-requests') }}" class="nav-link {{ request()->is('admin/leave-requests*') ? 'active' : '' }}"><i class="bi bi-airplane"></i> {{ __('Leave Requests') }}</a>
                </li>
            @endcan
            @can('manage-expense-claims')
                <li class="nav-item">
                    <a href="{{ url('/admin/expense-claims') }}" class="nav-link {{ request()->is('admin/expense-claims*') ? 'active' : '' }}"><i class="bi bi-receipt"></i> {{ __('Expense Claims') }}</a>
                </li>
            @endcan

            <div class="nav-section-label">{{ __('Insights') }}</div>
            <li class="nav-item">
                <a href="{{ url('/admin/announcements') }}" class="nav-link {{ request()->is('admin/announcements*') ? 'active' : '' }}"><i class="bi bi-broadcast"></i> {{ __('Announcements') }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/admin/targets') }}" class="nav-link {{ request()->is('admin/targets*') ? 'active' : '' }}"><i class="bi bi-bullseye"></i> {{ __('Targets') }}</a>
            </li>
            @can('view-analytics')
                <li class="nav-item">
                    <a href="{{ url('/admin/analytics') }}" class="nav-link {{ request()->is('admin/analytics*') ? 'active' : '' }}"><i class="bi bi-graph-up"></i> {{ __('Analytics') }}</a>
                </li>
            @endcan
            @canany(['view-analytics', 'review-reports'])
                <li class="nav-item">
                    <a href="{{ url('/admin/performance') }}" class="nav-link {{ request()->is('admin/performance*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i> {{ __('Performance') }}</a>
                </li>
            @endcanany
            @can('manage-forms')
                <li class="nav-item">
                    <a href="{{ url('/admin/forms') }}" class="nav-link {{ request()->is('admin/forms*') ? 'active' : '' }}"><i class="bi bi-ui-checks-grid"></i> {{ __('Forms') }}</a>
                </li>
            @endcan
            @can('manage-settings')
                <li class="nav-item">
                    <a href="{{ url('/admin/settings') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}"><i class="bi bi-gear"></i> {{ __('Settings') }}</a>
                </li>
            @endcan
            <li class="nav-item">
                <a href="{{ route('guide') }}" class="nav-link {{ request()->routeIs('guide') ? 'active' : '' }}"><i class="bi bi-life-preserver"></i> {{ __('How It Works') }}</a>
            </li>
        </ul>
        <hr class="text-white-50">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('language.switch', 'en') }}" class="btn btn-outline-light {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                <a href="{{ route('language.switch', 'ur') }}" class="btn btn-outline-light {{ app()->getLocale() === 'ur' ? 'active' : '' }}">اردو</a>
            </div>
        </div>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2 fs-5"></i> {{ auth()->user()->name }}
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>{{ __('Profile') }}</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out') }}</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <div class="flex-grow-1 d-flex flex-column" style="min-height: 100vh;">
        <div class="app-topbar px-4 py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-semibold">@yield('title', __('Dashboard'))</h4>
            @hasSection('topbar-actions')
                <div>@yield('topbar-actions')</div>
            @endif
        </div>

        <main class="flex-grow-1 p-4">
            @if (session('status'))
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
