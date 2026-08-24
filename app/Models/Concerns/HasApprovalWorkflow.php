<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared pending/approved/rejected review-transition shape used by
 * LeaveRequest and ExpenseClaim — both are "volunteer submits, a reviewer in
 * their chain of command decides" records with an identical status lifecycle.
 */
trait HasApprovalWorkflow
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function approve(User $reviewer): static
    {
        $this->update(['status' => 'approved', 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);

        return $this;
    }

    public function reject(User $reviewer): static
    {
        $this->update(['status' => 'rejected', 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()]);

        return $this;
    }

    /**
     * Everyone holding manage-leave-requests/manage-expense-claims whose
     * hierarchy scope actually covers this requester — the exact set of
     * people who will see this request in their review queue.
     */
    public static function reviewersFor(User $requester, string $permission): \Illuminate\Support\Collection
    {
        return User::permission($permission)->get()
            ->filter(fn (User $reviewer) => \App\Services\HierarchyScope::canView($reviewer, $requester));
    }
}
