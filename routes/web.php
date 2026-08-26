<?php

use App\Http\Controllers\Web\Admin\AnalyticsController;
use App\Http\Controllers\Web\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\DepartmentController;
use App\Http\Controllers\Web\Admin\ExpenseClaimController as AdminExpenseClaimController;
use App\Http\Controllers\Web\Admin\FormTemplateController;
use App\Http\Controllers\Web\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Web\Admin\MyTeamController;
use App\Http\Controllers\Web\Admin\NaController;
use App\Http\Controllers\Web\Admin\ProjectController;
use App\Http\Controllers\Web\Admin\PerformanceController as AdminPerformanceController;
use App\Http\Controllers\Web\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Web\Admin\ScheduledMeetingController as AdminScheduledMeetingController;
use App\Http\Controllers\Web\Admin\SearchController;
use App\Http\Controllers\Web\Admin\SettingController;
use App\Http\Controllers\Web\Admin\TargetController as AdminTargetController;
use App\Http\Controllers\Web\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Web\Admin\TaskReportController as AdminTaskReportController;
use App\Http\Controllers\Web\Admin\TeamController;
use App\Http\Controllers\Web\Admin\UcController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\Auth\NewPasswordController;
use App\Http\Controllers\Web\Auth\PasswordResetLinkController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\User\AnnouncementController as UserAnnouncementController;
use App\Http\Controllers\Web\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Web\User\ExpenseClaimController as UserExpenseClaimController;
use App\Http\Controllers\Web\User\LeaveRequestController as UserLeaveRequestController;
use App\Http\Controllers\Web\User\MeetingController as UserMeetingController;
use App\Http\Controllers\Web\User\PerformanceController as UserPerformanceController;
use App\Http\Controllers\Web\User\ProgressController;
use App\Http\Controllers\Web\User\ReportController as UserReportController;
use App\Http\Controllers\Web\User\ScheduledMeetingController as UserScheduledMeetingController;
use App\Http\Controllers\Web\User\TargetController as UserTargetController;
use App\Http\Controllers\Web\User\TaskController as UserTaskController;
use App\Http\Controllers\Web\User\VolunteerDocumentController as UserVolunteerDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect(auth()->user()->hasAnyRole(['super_admin', 'admin', 'na_head', 'uc_head']) ? '/admin' : '/dashboard');
    // Note: team_leader intentionally lands on /dashboard (they are an
    // elevated volunteer, not an admin) — extra nav items there link into
    // the /admin/reports, /admin/tasks, /admin/task-reports controllers for
    // their review duties, reusing the same controllers/views as Admin.
    // na_head/uc_head run their slice of the org much like Admin runs
    // theirs, so they land on /admin and are scoped down entirely via
    // HierarchyScope.
});

Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ur'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/guide', fn () => view('guide'))->name('guide');

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin|admin|na_head|uc_head|team_leader')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [SearchController::class, 'index'])->name('search.index');

        Route::get('/nas', [NaController::class, 'index'])->name('nas.index');
        Route::get('/nas/{na}', [NaController::class, 'show'])->name('nas.show');
        Route::get('/nas-compare', [NaController::class, 'compare'])->name('nas.compare');

        Route::middleware('permission:manage-nas')->group(function () {
            Route::post('/nas', [NaController::class, 'store'])->name('nas.store');
            Route::put('/nas/{na}', [NaController::class, 'update'])->name('nas.update');
            Route::delete('/nas/{na}', [NaController::class, 'destroy'])->name('nas.destroy');

            Route::resource('ucs', UcController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/{dailyReport}', [AdminReportController::class, 'show'])->name('reports.show');

        Route::middleware('permission:review-reports')->group(function () {
            Route::put('/reports/{dailyReport}/review', [AdminReportController::class, 'review'])->name('reports.review');
        });

        Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/targets', [AdminTargetController::class, 'index'])->name('targets.index');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('/performance', [AdminPerformanceController::class, 'index'])->name('performance.index');
        Route::get('/performance/{user}', [AdminPerformanceController::class, 'show'])->name('performance.show');

        Route::middleware('permission:manage-announcements')->group(function () {
            Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
            Route::put('/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
        });

        Route::middleware('permission:manage-targets')->group(function () {
            Route::post('/targets', [AdminTargetController::class, 'store'])->name('targets.store');
            Route::put('/targets/{target}', [AdminTargetController::class, 'update'])->name('targets.update');
            Route::delete('/targets/{target}', [AdminTargetController::class, 'destroy'])->name('targets.destroy');
        });

        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::middleware('permission:manage-users')->group(function () {
            Route::resource('users', UserController::class)->except(['show', 'index']);
            Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        });

        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->where('user', '[0-9]+');
        Route::post('/users/{user}/documents', [UserController::class, 'storeDocument'])->name('users.documents.store')->where('user', '[0-9]+');
        Route::delete('/documents/{document}', [UserController::class, 'destroyDocument'])->name('documents.destroy');

        Route::middleware('permission:manage-departments')->group(function () {
            Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        Route::middleware('permission:manage-teams')->group(function () {
            Route::resource('teams', TeamController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        Route::middleware('permission:manage-settings')->group(function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('permission:manage-meetings')->group(function () {
            Route::resource('meetings', AdminScheduledMeetingController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy'])
                ->parameters(['meetings' => 'scheduledMeeting']);

            Route::put('/meetings/{scheduledMeeting}/attendance', [AdminScheduledMeetingController::class, 'markAttendance'])
                ->name('meetings.attendance');
            Route::put('/meetings/{scheduledMeeting}/status', [AdminScheduledMeetingController::class, 'updateStatus'])
                ->name('meetings.status');
        });

        Route::middleware('permission:manage-tasks')->group(function () {
            Route::resource('tasks', AdminTaskController::class)->only(['index', 'show', 'store', 'update']);
        });

        Route::middleware('permission:manage-projects')->group(function () {
            Route::resource('projects', ProjectController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        });

        Route::middleware('permission:review-task-reports')->group(function () {
            Route::get('/task-reports', [AdminTaskReportController::class, 'index'])->name('task-reports.index');
            Route::get('/task-reports/{taskReport}', [AdminTaskReportController::class, 'show'])->name('task-reports.show');
            Route::put('/task-reports/{taskReport}/review', [AdminTaskReportController::class, 'review'])->name('task-reports.review');
        });

        Route::middleware('permission:manage-leave-requests')->group(function () {
            Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])->name('leave-requests.index');
            Route::get('/leave-requests/{leaveRequest}', [AdminLeaveRequestController::class, 'show'])->name('leave-requests.show');
            Route::put('/leave-requests/{leaveRequest}/review', [AdminLeaveRequestController::class, 'review'])->name('leave-requests.review');
        });

        Route::middleware('permission:manage-expense-claims')->group(function () {
            Route::get('/expense-claims', [AdminExpenseClaimController::class, 'index'])->name('expense-claims.index');
            Route::get('/expense-claims/{expenseClaim}', [AdminExpenseClaimController::class, 'show'])->name('expense-claims.show');
            Route::put('/expense-claims/{expenseClaim}/review', [AdminExpenseClaimController::class, 'review'])->name('expense-claims.review');
        });

        Route::middleware('permission:manage-forms')->group(function () {
            Route::resource('forms', FormTemplateController::class)->parameters(['forms' => 'formTemplate']);
        });

        Route::middleware('permission:manage-team')->group(function () {
            Route::get('/my-team', [MyTeamController::class, 'index'])->name('my-team.index');
        });
    });

    Route::prefix('dashboard')->name('user.')->middleware('role:user|team_leader')->group(function () {
        Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');

        Route::get('/reports', [UserReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/create', [UserReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [UserReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{dailyReport}/edit', [UserReportController::class, 'edit'])->name('reports.edit');
        Route::put('/reports/{dailyReport}', [UserReportController::class, 'update'])->name('reports.update');

        Route::get('/meetings', [UserMeetingController::class, 'index'])->name('meetings.index');
        Route::get('/meetings/{meeting}', [UserMeetingController::class, 'show'])->name('meetings.show');

        Route::get('/schedule', [UserScheduledMeetingController::class, 'index'])->name('schedule.index');
        Route::get('/schedule/{scheduledMeeting}', [UserScheduledMeetingController::class, 'show'])->name('schedule.show');

        Route::get('/tasks', [UserTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [UserTaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{task}/report', [UserTaskController::class, 'submitReport'])->name('tasks.submit-report');
        Route::post('/tasks/{task}/form-response', [UserTaskController::class, 'submitFormResponse'])->name('tasks.submit-form-response');

        Route::get('/targets', [UserTargetController::class, 'index'])->name('targets.index');
        Route::get('/announcements', [UserAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
        Route::get('/performance', [UserPerformanceController::class, 'index'])->name('performance.index');

        Route::get('/leave-requests', [UserLeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::post('/leave-requests', [UserLeaveRequestController::class, 'store'])->name('leave-requests.store');

        Route::get('/expense-claims', [UserExpenseClaimController::class, 'index'])->name('expense-claims.index');
        Route::post('/expense-claims', [UserExpenseClaimController::class, 'store'])->name('expense-claims.store');

        Route::get('/documents', [UserVolunteerDocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents', [UserVolunteerDocumentController::class, 'store'])->name('documents.store');
        Route::delete('/documents/{document}', [UserVolunteerDocumentController::class, 'destroy'])->name('documents.destroy');
    });
});
