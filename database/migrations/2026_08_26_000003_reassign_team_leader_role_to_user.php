<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The team_leader role is being removed entirely. Every user currently
 * holding it becomes a plain Volunteer instead — losing the review/manage
 * abilities that came with leading a team, but keeping everything else
 * (their own reports, tasks, meetings, leave/expense history).
 */
return new class extends Migration
{
    public function up(): void
    {
        $teamLeaderRoleIds = DB::table('roles')->where('name', 'team_leader')->pluck('id', 'guard_name');
        $userRoleIds = DB::table('roles')->where('name', 'user')->pluck('id', 'guard_name');

        foreach ($teamLeaderRoleIds as $guard => $teamLeaderRoleId) {
            $userRoleId = $userRoleIds[$guard] ?? null;

            if ($userRoleId === null) {
                continue;
            }

            $assignments = DB::table('model_has_roles')->where('role_id', $teamLeaderRoleId)->get();

            foreach ($assignments as $assignment) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $userRoleId,
                    'model_type' => $assignment->model_type,
                    'model_id' => $assignment->model_id,
                ]);
            }

            DB::table('model_has_roles')->where('role_id', $teamLeaderRoleId)->delete();
        }

        DB::table('roles')->where('name', 'team_leader')->delete();
    }

    public function down(): void
    {
        // Not reversible — which users originally held team_leader is lost
        // once they're reassigned to user above.
    }
};
