<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VolunteerDocument;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VolunteerDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, OrganizationSeeder::class, DemoUserSeeder::class]);
        Storage::fake('public');
    }

    public function test_volunteer_can_upload_and_view_their_own_document(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->post('/dashboard/documents', [
            'title' => 'CNIC Copy',
            'document_type' => 'cnic',
            'file' => UploadedFile::fake()->create('cnic.pdf', 100),
        ])->assertRedirect();

        $document = VolunteerDocument::where('user_id', $volunteer->id)->first();
        $this->assertNotNull($document);
        $this->assertNotNull($document->file);

        $this->actingAs($volunteer)->get('/dashboard/documents')->assertOk()->assertSee('CNIC Copy');
    }

    public function test_admin_can_view_and_upload_documents_for_a_volunteer_in_their_department(): void
    {
        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($admin)->get("/admin/users/{$volunteer->id}")->assertOk();

        $this->actingAs($admin)->post("/admin/users/{$volunteer->id}/documents", [
            'title' => 'Training Certificate',
            'document_type' => 'certificate',
            'file' => UploadedFile::fake()->create('cert.pdf', 100),
        ])->assertRedirect();

        $this->assertDatabaseHas('volunteer_documents', [
            'user_id' => $volunteer->id,
            'title' => 'Training Certificate',
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_admin_from_another_department_cannot_view_a_volunteers_documents(): void
    {
        $otherAdmin = User::where('email', 'admin2@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($otherAdmin)->get("/admin/users/{$volunteer->id}")->assertForbidden();
    }

    public function test_volunteer_cannot_delete_another_volunteers_document(): void
    {
        $volunteer = User::where('email', 'volunteer1@example.com')->first();
        $otherVolunteer = User::where('email', 'volunteer3@example.com')->first();

        $document = VolunteerDocument::create([
            'user_id' => $otherVolunteer->id,
            'title' => 'Private Doc',
            'document_type' => 'other',
            'uploaded_by' => $otherVolunteer->id,
        ]);

        $this->actingAs($volunteer)->delete("/dashboard/documents/{$document->id}")->assertForbidden();
    }
}
