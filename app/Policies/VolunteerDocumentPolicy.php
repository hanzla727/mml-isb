<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VolunteerDocument;
use App\Services\HierarchyScope;

class VolunteerDocumentPolicy
{
    /**
     * The volunteer themselves, their team leader, their department's admin
     * (via HierarchyScope), and super_admin — no one else may see a
     * volunteer's documents.
     */
    public function view(User $user, VolunteerDocument $volunteerDocument): bool
    {
        return HierarchyScope::canView($user, $volunteerDocument->user);
    }

    public function uploadFor(User $user, User $target): bool
    {
        return HierarchyScope::canView($user, $target);
    }

    public function delete(User $user, VolunteerDocument $volunteerDocument): bool
    {
        return HierarchyScope::canView($user, $volunteerDocument->user);
    }
}
