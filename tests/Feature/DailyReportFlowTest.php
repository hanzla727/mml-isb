<?php

namespace Tests\Feature;

use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_user_can_submit_a_report_with_meetings_and_it_computes_hours_and_upserts_contacts(): void
    {
        $token = $this->loginAs('volunteer1@example.com');

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/reports', [
            'report_date' => '2026-07-23',
            'field_start_time' => '09:00',
            'field_end_time' => '17:30',
            'status' => 'submitted',
            'summary' => 'Visited two families.',
            'meetings' => [
                [
                    'name' => 'Ahmed Raza',
                    'phone' => '03001234567',
                    'category' => 'family_visit',
                    'discussion' => 'Discussed household needs.',
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.total_hours', '8.50');
        $response->assertJsonPath('data.status', 'submitted');
        $response->assertJsonPath('data.meetings.0.contact.phone', '03001234567');

        $this->assertDatabaseHas('contacts', ['phone' => '03001234567', 'name' => 'Ahmed Raza']);

        // Submitting a second meeting for an existing contact by phone must reuse it, not duplicate.
        $secondReport = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/reports', [
            'report_date' => '2026-07-24',
            'field_start_time' => '09:00',
            'field_end_time' => '13:00',
            'status' => 'submitted',
            'meetings' => [
                [
                    'contact_id' => $response->json('data.meetings.0.contact.id'),
                    'category' => 'follow_up',
                ],
            ],
        ]);

        $secondReport->assertCreated();
        $this->assertSame(1, \App\Models\Contact::where('phone', '03001234567')->count());
    }

    public function test_user_cannot_access_admin_routes(): void
    {
        $token = $this->loginAs('volunteer1@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_view_all_reports_but_not_create_announcements(): void
    {
        $token = $this->loginAs('admin1@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/reports')
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/announcements', [
                'title' => 'Test',
                'body' => 'Test body',
                'category' => 'general',
                'audience_scope' => 'all',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_manage_announcements_and_targets(): void
    {
        $token = $this->loginAs('superadmin@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/announcements', [
                'title' => 'Weekly Meeting Reminder',
                'body' => 'Meeting at 5pm Friday.',
                'category' => 'meeting_reminder',
                'audience_scope' => 'all',
            ])
            ->assertCreated();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/targets', [
                'title' => 'Work 10 hours daily',
                'type' => 'daily',
                'metric' => 'hours',
                'target_value' => 10,
                'scope' => 'all',
                'start_date' => '2026-07-23',
            ])
            ->assertCreated()
            ->assertJsonPath('data.is_active', true);
    }

    private function loginAs(string $email): string
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => $email,
            'pin' => '1234',
        ]);

        $response->assertOk();

        return $response->json('token');
    }
}
