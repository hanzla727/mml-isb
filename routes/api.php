<?php

use App\Http\Controllers\Api\Admin\DepartmentController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseClaimController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\OrgDirectoryController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ScheduledMeetingController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskReportController;
use App\Http\Controllers\Api\VolunteerDirectoryController;
use App\Http\Controllers\Api\VolunteerDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/performance', [PerformanceController::class, 'index']);

    Route::get('/reports', [DailyReportController::class, 'index']);
    Route::get('/my-reports', [DailyReportController::class, 'myReports']);
    Route::post('/reports', [DailyReportController::class, 'store']);
    Route::get('/reports/{dailyReport}', [DailyReportController::class, 'show']);
    Route::put('/reports/{dailyReport}', [DailyReportController::class, 'update']);

    Route::middleware('permission:review-reports')->group(function () {
        Route::put('/reports/{dailyReport}/review', [DailyReportController::class, 'review']);
    });

    Route::get('/my-meetings', [MeetingController::class, 'myMeetings']);
    Route::get('/my-meetings/{meeting}', [MeetingController::class, 'show']);
    Route::post('/my-meetings/{meeting}/read', [MeetingController::class, 'markRead']);

    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts/{contact}', [ContactController::class, 'show']);

    Route::get('/targets', [TargetController::class, 'index']);

    Route::get('/volunteers', [VolunteerDirectoryController::class, 'index']);
    Route::get('/departments', [OrgDirectoryController::class, 'departments']);

    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements/{announcement}/read', [AnnouncementController::class, 'markRead']);

    // Phase 4: scheduled meetings + tasks. Distinct paths from Phase 3's
    // /api/my-meetings (field-visit contacts logged in daily reports).
    Route::get('/meetings', [ScheduledMeetingController::class, 'index']);
    Route::get('/meetings/{scheduledMeeting}', [ScheduledMeetingController::class, 'show']);
    Route::post('/meetings', [ScheduledMeetingController::class, 'store']);
    Route::put('/meetings/{scheduledMeeting}', [ScheduledMeetingController::class, 'update']);
    Route::delete('/meetings/{scheduledMeeting}', [ScheduledMeetingController::class, 'destroy']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::post('/tasks/{task}/reports', [TaskReportController::class, 'store']);
    Route::get('/tasks/{task}/reports', [TaskReportController::class, 'index']);
    Route::post('/tasks/{task}/comments', [TaskController::class, 'comments']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::middleware('permission:submit-leave-requests')->group(function () {
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    });

    Route::get('/expense-claims', [ExpenseClaimController::class, 'index']);
    Route::middleware('permission:submit-expense-claims')->group(function () {
        Route::post('/expense-claims', [ExpenseClaimController::class, 'store']);
    });

    Route::get('/documents', [VolunteerDocumentController::class, 'index']);
    Route::post('/documents', [VolunteerDocumentController::class, 'store']);
    Route::delete('/documents/{document}', [VolunteerDocumentController::class, 'destroy']);

    Route::prefix('admin')->group(function () {
        Route::middleware('permission:manage-targets')->group(function () {
            Route::post('/targets', [TargetController::class, 'store']);
            Route::put('/targets/{target}', [TargetController::class, 'update']);
            Route::delete('/targets/{target}', [TargetController::class, 'destroy']);
        });

        Route::middleware('permission:manage-announcements')->group(function () {
            Route::post('/announcements', [AnnouncementController::class, 'store']);
        });

        Route::middleware('permission:manage-tasks')->group(function () {
            Route::post('/tasks', [TaskController::class, 'store']);
            Route::put('/tasks/{task}', [TaskController::class, 'update']);
        });

        Route::middleware('permission:review-task-reports')->group(function () {
            Route::get('/task-reports', [TaskReportController::class, 'pending']);
            Route::put('/task-reports/{taskReport}/review', [TaskReportController::class, 'review']);
        });

        Route::middleware('permission:manage-leave-requests')->group(function () {
            Route::get('/leave-requests', [LeaveRequestController::class, 'adminIndex']);
            Route::put('/leave-requests/{leaveRequest}/review', [LeaveRequestController::class, 'review']);
        });

        Route::middleware('permission:manage-expense-claims')->group(function () {
            Route::get('/expense-claims', [ExpenseClaimController::class, 'adminIndex']);
            Route::put('/expense-claims/{expenseClaim}/review', [ExpenseClaimController::class, 'review']);
        });

        Route::middleware('permission:manage-users')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
            Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
        });

        Route::middleware('permission:manage-departments')->group(function () {
            Route::apiResource('departments', DepartmentController::class)->except(['show']);
        });
    });
});
