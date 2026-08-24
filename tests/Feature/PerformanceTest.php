<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_can_view_performance_list_and_a_volunteers_summary(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        DailyReport::create([
            'user_id' => $volunteer->id,
            'report_date' => now()->subDays(2)->toDateString(),
            'field_start_time' => '09:00',
            'field_end_time' => '17:00',
            'total_hours' => 8,
            'status' => 'submitted',
            'review_status' => 'approved',
        ]);

        $this->actingAs($admin)->get('/admin/performance')->assertOk()->assertSee($volunteer->name);
        $this->actingAs($admin)->get("/admin/performance/{$volunteer->id}")->assertOk()->assertSee('Reports Approved');
    }

    public function test_admin_cannot_view_performance_of_volunteer_in_another_area(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first(); // NA-48 only.
        $otherVolunteer = User::where('email', 'volunteer5@example.com')->first(); // NA-49.

        $this->actingAs($admin)->get("/admin/performance/{$otherVolunteer->id}")->assertForbidden();
    }

    public function test_volunteer_can_view_their_own_performance_page(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->get('/dashboard/performance')->assertOk()->assertSee('My Performance');
    }
}
