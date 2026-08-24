<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestDecidedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_volunteer_submits_leave_request_and_their_departments_admin_is_notified(): void
    {
        Notification::fake();

        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->post('/dashboard/leave-requests', [
            'leave_type' => 'sick',
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'reason' => 'Not feeling well.',
        ])->assertRedirect();

        $leaveRequest = LeaveRequest::where('user_id', $volunteer->id)->first();
        $this->assertSame('pending', $leaveRequest->status);

        // Leave/expense management is Admin + Super Admin only (not Team
        // Leader — team_leader only holds submit-leave-requests).
        $admin = User::where('email', 'admin1@example.com')->first();
        $teamLeader = User::where('email', 'teamleader1@example.com')->first();

        Notification::assertSentTo($admin, LeaveRequestSubmittedNotification::class);
        Notification::assertNotSentTo($teamLeader, LeaveRequestSubmittedNotification::class);
    }

    public function test_admin_can_approve_a_leave_request_and_volunteer_is_notified(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $leaveRequest = LeaveRequest::create([
            'user_id' => $volunteer->id,
            'leave_type' => 'vacation',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
        ]);

        $this->actingAs($admin)->put("/admin/leave-requests/{$leaveRequest->id}/review", [
            'decision' => 'approve',
        ])->assertRedirect();

        $this->assertSame('approved', $leaveRequest->fresh()->status);
        $this->assertSame($admin->id, $leaveRequest->fresh()->reviewed_by);

        Notification::assertSentTo($volunteer, LeaveRequestDecidedNotification::class);
    }

    public function test_admin_from_another_department_cannot_review_the_leave_request(): void
    {
        $otherAdmin = User::where('email', 'admin2@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $leaveRequest = LeaveRequest::create([
            'user_id' => $volunteer->id,
            'leave_type' => 'personal',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($otherAdmin)->put("/admin/leave-requests/{$leaveRequest->id}/review", [
            'decision' => 'approve',
        ])->assertForbidden();
    }
}
