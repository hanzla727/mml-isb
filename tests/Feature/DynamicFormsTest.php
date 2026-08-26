<?php

namespace Tests\Feature;

use App\Models\FormTemplate;
use App\Models\ScheduledMeeting;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_builds_a_form_template_attaches_it_to_a_task_and_volunteer_submits_it(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/forms', [
            'name' => 'Field Visit Checklist',
            'description' => 'Post-visit checklist.',
            'fields' => [
                ['label' => 'Location Visited', 'field_type' => 'text', 'is_required' => '1'],
                ['label' => 'Outcome', 'field_type' => 'dropdown', 'options' => 'Successful, Follow-up Needed, Cancelled', 'is_required' => '1'],
            ],
        ])->assertRedirect();

        $formTemplate = FormTemplate::where('name', 'Field Visit Checklist')->first();
        $this->assertNotNull($formTemplate);
        $this->assertCount(2, $formTemplate->fields);

        $meeting = ScheduledMeeting::create([
            'title' => 'Base Meeting', 'meeting_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00', 'organizer_id' => $admin->id,
            'status' => 'upcoming', 'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post('/admin/tasks', [
            'scheduled_meeting_id' => $meeting->id,
            'form_template_id' => $formTemplate->id,
            'title' => 'Visit Family X',
            'priority' => 'medium',
            'scope' => 'individual',
            'user_ids' => [$volunteer->id],
        ])->assertRedirect();

        $task = Task::where('title', 'Visit Family X')->first();
        $this->assertSame($formTemplate->id, $task->form_template_id);

        $textField = $formTemplate->fields->firstWhere('label', 'Location Visited');
        $dropdownField = $formTemplate->fields->firstWhere('label', 'Outcome');

        $this->actingAs($volunteer)->get("/dashboard/tasks/{$task->id}")->assertOk()->assertSee('Field Visit Checklist');

        $this->actingAs($volunteer)->post("/dashboard/tasks/{$task->id}/form-response", [
            "field_{$textField->id}" => 'Smith household',
            "field_{$dropdownField->id}" => 'Successful',
        ])->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_template_id' => $formTemplate->id,
            'submittable_type' => Task::class,
            'submittable_id' => $task->id,
            'user_id' => $volunteer->id,
        ]);
        $this->assertDatabaseHas('form_submission_values', ['value' => 'Smith household']);
        $this->assertDatabaseHas('form_submission_values', ['value' => 'Successful']);

        $this->actingAs($admin)->get("/admin/forms/{$formTemplate->id}")->assertOk()->assertSee('Smith household');
    }

    public function test_missing_required_field_is_rejected(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->post('/admin/forms', [
            'name' => 'Quick Form',
            'fields' => [
                ['label' => 'Notes', 'field_type' => 'text', 'is_required' => '1'],
            ],
        ])->assertRedirect();

        $formTemplate = FormTemplate::where('name', 'Quick Form')->first();

        $task = Task::create([
            'form_template_id' => $formTemplate->id,
            'title' => 'Task With Form',
            'priority' => 'medium',
            'status' => 'assigned',
            'created_by' => $admin->id,
        ]);
        $task->assignees()->attach($volunteer->id);

        $field = $formTemplate->fields->first();

        $this->actingAs($volunteer)->post("/dashboard/tasks/{$task->id}/form-response", [
            "field_{$field->id}" => '',
        ])->assertSessionHasErrors(["field_{$field->id}"]);
    }
}
