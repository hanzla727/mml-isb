<?php

namespace Tests\Feature;

use App\Models\ExpenseClaim;
use App\Models\User;
use App\Notifications\ExpenseClaimDecidedNotification;
use App\Notifications\ExpenseClaimSubmittedNotification;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DepartmentTeamSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExpenseClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, DepartmentTeamSeeder::class, DemoUserSeeder::class]);
    }

    public function test_volunteer_submits_expense_claim_and_departments_admin_is_notified(): void
    {
        Notification::fake();

        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $this->actingAs($volunteer)->post('/dashboard/expense-claims', [
            'expense_type' => 'travel',
            'amount' => '45.50',
            'date' => now()->toDateString(),
            'description' => 'Bus fare to field site.',
        ])->assertRedirect();

        $expenseClaim = ExpenseClaim::where('user_id', $volunteer->id)->first();
        $this->assertSame('pending', $expenseClaim->status);
        $this->assertSame('45.50', $expenseClaim->amount);

        $admin = User::where('email', 'admin1@example.com')->first();
        Notification::assertSentTo($admin, ExpenseClaimSubmittedNotification::class);
    }

    public function test_admin_can_reject_an_expense_claim_and_volunteer_is_notified(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin1@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $expenseClaim = ExpenseClaim::create([
            'user_id' => $volunteer->id,
            'expense_type' => 'supplies',
            'amount' => 20,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->put("/admin/expense-claims/{$expenseClaim->id}/review", [
            'decision' => 'reject',
        ])->assertRedirect();

        $this->assertSame('rejected', $expenseClaim->fresh()->status);
        Notification::assertSentTo($volunteer, ExpenseClaimDecidedNotification::class);
    }

    public function test_admin_from_another_department_cannot_review_the_expense_claim(): void
    {
        $otherAdmin = User::where('email', 'admin2@example.com')->first();
        $volunteer = User::where('email', 'volunteer1@example.com')->first();

        $expenseClaim = ExpenseClaim::create([
            'user_id' => $volunteer->id,
            'expense_type' => 'food',
            'amount' => 10,
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($otherAdmin)->put("/admin/expense-claims/{$expenseClaim->id}/review", [
            'decision' => 'approve',
        ])->assertForbidden();
    }
}
