<?php

namespace App\Services;

use App\Models\Uc;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for the Super Admin → Admin → NA Head → UC Head →
 * Team Leader → Volunteer visibility hierarchy. Every controller that
 * queries reports/tasks/meetings/volunteers goes through here instead of
 * repeating na/uc/department/team scoping logic.
 *
 * Admin and UC Head are many-to-many (an Admin can be assigned several NAs
 * via User::adminNas(), a UC Head several UCs via User::ucsHeaded()), and a
 * Team Leader can likewise lead more than one Team via User::teamsLed() —
 * only NA Head owns exactly one NA.
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
        } elseif ($viewer->hasRole('uc_head')) {
            $ucIds = $viewer->ucsHeaded()->pluck('ucs.id');
            $ids = User::whereIn('uc_id', $ucIds)->pluck('id')->all();
        } elseif ($viewer->hasRole('team_leader')) {
            $teamIds = $viewer->teamsLed()->pluck('id');
            $ids = User::whereIn('team_id', $teamIds)->pluck('id')->all();
        } else {
            $ids = [];
        }

        // An Admin/NA Head/UC Head isn't necessarily a "member" of an
        // NA/UC themselves (Admin especially — they hold access via the
        // admin_na pivot, not an na_id), but they must always be able to
        // see their own records regardless of role.
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

        if ($viewer->hasRole('uc_head')) {
            return $target->uc_id !== null && $viewer->ucsHeaded()->where('ucs.id', $target->uc_id)->exists();
        }

        if ($viewer->hasRole('team_leader')) {
            return $target->team_id !== null && $viewer->teamsLed()->where('id', $target->team_id)->exists();
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

        if ($viewer->hasRole('uc_head')) {
            return Uc::whereIn('id', $viewer->ucsHeaded()->pluck('ucs.id'))
                ->pluck('na_id')
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * UCs the viewer can see. For every role except UC Head this is derived
     * from their visible NAs — for a UC Head it's their exact assigned UCs,
     * NOT every UC in the same NA (they may only be responsible for some of
     * them), so that case is resolved directly rather than falling through
     * to the NA-derived logic below.
     *
     * @return int[]|null null means unrestricted (Super Admin).
     */
    public static function visibleUcIds(User $viewer): ?array
    {
        if ($viewer->hasRole('super_admin')) {
            return null;
        }

        if ($viewer->hasRole('uc_head')) {
            return $viewer->ucsHeaded()->pluck('ucs.id')->all();
        }

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
