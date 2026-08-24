<?php

namespace App\Services;

use App\Models\Uc;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for the Super Admin → Admin → NA Head → Team
 * Leader → Volunteer visibility hierarchy. Every controller that queries
 * reports/tasks/meetings/volunteers goes through here instead of repeating
 * na/uc/department/team scoping logic.
 *
 * Admin differs from every other scoped role: it's a many-to-many (an Admin
 * can be assigned several NAs via User::adminNas()), whereas NA Head and
 * Team Leader each own exactly one NA/Team.
 */
class HierarchyScope
{
    /**
     * @return int[]|null null means unrestricted (Super Admin).
     */
    public static function visibleUserIds(User $viewer): ?array
    {
        if ($viewer->hasRole('super_admin')) {
            return null;
        }

        if ($viewer->hasRole('admin')) {
            $naIds = $viewer->adminNas()->pluck('nas.id');
            $ids = User::whereIn('na_id', $naIds)->pluck('id')->all();
        } elseif ($viewer->hasRole('na_head')) {
            $ids = User::where('na_id', $viewer->na_id)->pluck('id')->all();
        } elseif ($viewer->hasRole('team_leader')) {
            $ids = User::where('team_id', $viewer->team_id)->pluck('id')->all();
        } else {
            $ids = [];
        }

        // An Admin/NA Head isn't necessarily a "member" of an NA themselves
        // (Admin especially — they hold access via the admin_na pivot, not
        // an na_id), but they must always be able to see their own records
        // regardless of role.
        $ids[] = $viewer->id;

        return array_values(array_unique($ids));
    }

    public static function canView(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id || $viewer->hasRole('super_admin')) {
            return true;
        }

        if ($viewer->hasRole('admin')) {
            return $target->na_id !== null
                && $viewer->adminNas()->where('nas.id', $target->na_id)->exists();
        }

        if ($viewer->hasRole('na_head')) {
            return $target->na_id !== null && $target->na_id === $viewer->na_id;
        }

        if ($viewer->hasRole('team_leader')) {
            return $target->team_id !== null && $target->team_id === $viewer->team_id;
        }

        return false;
    }

    /**
     * @return int[]|null null means unrestricted (Super Admin).
     */
    public static function visibleNaIds(User $viewer): ?array
    {
        if ($viewer->hasRole('super_admin')) {
            return null;
        }

        if ($viewer->hasRole('admin')) {
            return $viewer->adminNas()->pluck('nas.id')->all();
        }

        if ($viewer->hasRole('na_head') && $viewer->na_id !== null) {
            return [$viewer->na_id];
        }

        return [];
    }

    /**
     * UCs the viewer can see, derived from their visible NAs — for scoping
     * models keyed by uc_id (Team, Project) rather than na_id directly.
     *
     * @return int[]|null null means unrestricted (Super Admin).
     */
    public static function visibleUcIds(User $viewer): ?array
    {
        $naIds = static::visibleNaIds($viewer);

        if ($naIds === null) {
            return null;
        }

        return Uc::whereIn('na_id', $naIds)->pluck('id')->all();
    }

    public static function restrictUsersQuery(Builder $query, User $viewer): Builder
    {
        $ids = static::visibleUserIds($viewer);

        return $ids === null ? $query : $query->whereIn('id', $ids);
    }

    /**
     * For any query on a model with a direct user_id column (DailyReport,
     * LeaveRequest, ExpenseClaim, VolunteerDocument, ...).
     */
    public static function restrictByOwner(Builder $query, User $viewer, string $ownerColumn = 'user_id'): Builder
    {
        $ids = static::visibleUserIds($viewer);

        return $ids === null ? $query : $query->whereIn($ownerColumn, $ids);
    }

    /**
     * For models related to users via a many-to-many relation (Task
     * assignees, ScheduledMeeting participants).
     */
    public static function restrictByRelation(Builder $query, User $viewer, string $relation): Builder
    {
        $ids = static::visibleUserIds($viewer);

        return $ids === null ? $query : $query->whereHas($relation, fn ($q) => $q->whereIn('users.id', $ids));
    }

    /**
     * For any query on a model with a direct na_id column (Na itself via
     * 'id', or anything denormalized with one).
     */
    public static function restrictByNa(Builder $query, User $viewer, string $naColumn = 'na_id'): Builder
    {
        $ids = static::visibleNaIds($viewer);

        return $ids === null ? $query : $query->whereIn($naColumn, $ids);
    }

    /**
     * For any query on a model with a direct uc_id column (Team, Project,
     * or Uc itself via 'id').
     */
    public static function restrictByUc(Builder $query, User $viewer, string $ucColumn = 'uc_id'): Builder
    {
        $ids = static::visibleUcIds($viewer);

        return $ids === null ? $query : $query->whereIn($ucColumn, $ids);
    }
}
