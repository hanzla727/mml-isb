<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_can_search_contacts_and_tasks_scoped_to_their_area(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $otherAdmin = User::where('email', 'admin2@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        Contact::create(['name' => 'Zulfiqar Searchable', 'phone' => '0300', 'created_by' => $admin->id]);
        Contact::create(['name' => 'Zulfiqar Other Dept', 'phone' => '0301', 'created_by' => $otherAdmin->id]);

        $task = Task::create(['title' => 'Searchable Task', 'priority' => 'medium', 'status' => 'assigned', 'created_by' => $admin->id]);
        $task->assignees()->attach($volunteer->id);

        $response = $this->actingAs($admin)->get('/admin/search?q=Zulfiqar');
        $response->assertOk()->assertSee('Zulfiqar Searchable')->assertDontSee('Zulfiqar Other Dept');

        $this->actingAs($admin)->get('/admin/search?q=Searchable Task')->assertOk()->assertSee('Searchable Task');
    }

    public function test_empty_query_shows_no_results_sections(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();

        $this->actingAs($admin)->get('/admin/search')->assertOk();
    }
}
