<?php

namespace Database\Seeders;

use App\Models\Na;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password',
        ]);
        $superAdmin->assignRole('super_admin');

        $na48 = Na::where('name', 'NA-48')->firstOrFail();
        $na49 = Na::where('name', 'NA-49')->firstOrFail();

        // Admin: assigned to one or more NAs via the admin_na pivot — this
        // is what actually scopes what they can see, not department_id.
        $admins = [
            ['name' => 'Admin One', 'email' => 'admin1@example.com', 'nas' => [$na48]],
            ['name' => 'Admin Two', 'email' => 'admin2@example.com', 'nas' => [$na49]],
        ];

        foreach ($admins as $data) {
            $admin = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => 'password',
            ]);
            $admin->assignRole('admin');
            $admin->adminNas()->attach(collect($data['nas'])->pluck('id'));
        }

        // NA Head: scoped to exactly one NA (na_id, no single uc_id — every
        // UC under the NA is their responsibility), and that NA points back
        // at them (na_head_id) — kept in sync both ways, same pattern as
        // Team::leader_id <-> User::team_id below.
        $naHead = User::factory()->create([
            'name' => 'NA Head One',
            'email' => 'nahead1@example.com',
            'password' => 'password',
            'na_id' => $na48->id,
        ]);
        $naHead->assignRole('na_head');
        $na48->update(['na_head_id' => $naHead->id]);

        $teams = Team::with('uc.na')->orderBy('id')->get();
        $donorRelationsTeam = $teams->firstWhere('name', 'Donor Relations Team');

        $teamLeader = User::factory()->create([
            'name' => 'Team Leader One',
            'email' => 'teamleader1@example.com',
            'password' => 'password',
            'na_id' => $donorRelationsTeam->uc->na_id,
            'uc_id' => $donorRelationsTeam->uc_id,
            'department_id' => $donorRelationsTeam->department_id,
            'team_id' => $donorRelationsTeam->id,
            'reporting_head_id' => $naHead->id,
        ]);
        $teamLeader->assignRole('team_leader');
        $donorRelationsTeam->update(['leader_id' => $teamLeader->id]);

        for ($i = 1; $i <= 8; $i++) {
            $team = $teams->get(($i - 1) % $teams->count());

            $user = User::factory()->create([
                'name' => "Volunteer {$i}",
                'email' => "volunteer{$i}@example.com",
                'password' => 'password',
                'na_id' => $team->uc->na_id,
                'uc_id' => $team->uc_id,
                'department_id' => $team->department_id,
                'team_id' => $team->id,
                'reporting_head_id' => $team->leader_id ?? $team->uc->na->na_head_id,
            ]);
            $user->assignRole('user');
        }
    }
}
