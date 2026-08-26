<?php

namespace App\Services;

use App\Models\User;

/**
 * Shared "who does this apply to" resolution for meeting participants and
 * task assignees. Resolves a scope selection (individual users / a whole
 * team / a whole department / a whole UC / a whole NA / several NAs /
 * everyone) into a concrete list of user IDs *once*, at creation time —
 * matching how Phase 3 already resolves "select all volunteers" for
 * daily-report meetings, rather than dynamically re-evaluating membership
 * on every read (so someone joining a team later doesn't retroactively
 * appear on an older meeting/task).
 */
class AudienceResolver
{
    /**
     * @return int[]
     */
    public static function resolve(string $scope, array $data): array
    {
        return match ($scope) {
            'individual', 'users' => array_values(array_unique(array_map('intval', $data['user_ids'] ?? []))),
            'team' => User::query()->where('team_id', $data['team_id'] ?? null)->where('is_active', true)->pluck('id')->all(),
            'teams' => User::query()->whereIn('team_id', $data['team_ids'] ?? [])->where('is_active', true)->pluck('id')->all(),
            'department' => User::query()->where('department_id', $data['department_id'] ?? null)->where('is_active', true)->pluck('id')->all(),
            'departments' => User::query()->whereIn('department_id', $data['department_ids'] ?? [])->where('is_active', true)->pluck('id')->all(),
            'uc' => User::query()->where('uc_id', $data['uc_id'] ?? null)->where('is_active', true)->pluck('id')->all(),
            'na' => User::query()->where('na_id', $data['na_id'] ?? null)->where('is_active', true)->pluck('id')->all(),
            'nas' => User::query()->whereIn('na_id', $data['na_ids'] ?? [])->where('is_active', true)->pluck('id')->all(),
            'all' => User::query()->where('is_active', true)->pluck('id')->all(),
            default => [],
        };
    }
}
