<?php

namespace Database\Seeders;

use App\Models\Na;
use App\Models\Team;
use App\Models\Uc;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Ahmad Raza',
            'email' => 'superadmin@example.com',
            'password' => 'password',
        ]);
        $superAdmin->assignRole('super_admin');

        $na48 = Na::where('name', 'NA-48')->firstOrFail();
        $na49 = Na::where('name', 'NA-49')->firstOrFail();
        $na50 = Na::where('name', 'NA-50')->firstOrFail();

        // Admin: assigned to one or more NAs via the admin_na pivot — this
        // is what actually scopes what they can see, not department_id.
        // Admin One covers two NAs at once (NA-48 and NA-50) to demonstrate
        // that an Admin isn't limited to a single NA.
        $admins = [
            ['name' => 'Kamran Sheikh', 'email' => 'admin1@example.com', 'nas' => [$na48, $na50]],
            ['name' => 'Sadia Farooq', 'email' => 'admin2@example.com', 'nas' => [$na49]],
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
        // Team::leader_id <-> User::team_id below. NA-50 is deliberately
        // left without a dedicated NA Head — it's run by Admin + its UC
        // Head instead, showing that's a valid, real configuration too.
        $naHeads = [
            ['name' => 'Tariq Mehmood', 'email' => 'nahead1@example.com', 'na' => $na48],
            ['name' => 'Farah Naz', 'email' => 'nahead2@example.com', 'na' => $na49],
        ];

        foreach ($naHeads as $data) {
            $naHead = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => 'password',
                'na_id' => $data['na']->id,
            ]);
            $naHead->assignRole('na_head');
            $data['na']->update(['na_head_id' => $naHead->id]);
        }

        // UC Head: responsible for one or more specific UCs (via the
        // uc_heads pivot), regardless of which NA(s) they sit in. Sarah
        // Iqbal here covers both of NA-50's staffed UCs at once.
        $ucBharaKahu = Uc::where('name', 'UC Bhara Kahu')->firstOrFail();
        $ucTarnol = Uc::where('name', 'UC Tarnol')->firstOrFail();

        $ucHead = User::factory()->create([
            'name' => 'Sarah Iqbal',
            'email' => 'uchead1@example.com',
            'password' => 'password',
            'na_id' => $na50->id,
        ]);
        $ucHead->assignRole('uc_head');
        $ucHead->ucsHeaded()->attach([$ucBharaKahu->id, $ucTarnol->id]);

        $teams = Team::with('uc.na')->orderBy('id')->get();

        // Team Leader: leads one or more Teams (via Team::leader_id — a
        // Team Leader is never limited to exactly one team). Spread across
        // all three NAs so every NA has at least one staffed, led team.
        $teamLeaders = [
            ['name' => 'Bilal Chaudhry', 'email' => 'teamleader1@example.com', 'team' => 'Donor Relations Team', 'reports_to' => fn () => Na::where('name', 'NA-48')->firstOrFail()->na_head_id],
            ['name' => 'Ayesha Siddiqui', 'email' => 'teamleader2@example.com', 'team' => 'Hospital Volunteers Team', 'reports_to' => fn () => Na::where('name', 'NA-48')->firstOrFail()->na_head_id],
            ['name' => 'Hassan Raza', 'email' => 'teamleader3@example.com', 'team' => 'Community Fundraising Team', 'reports_to' => fn () => Na::where('name', 'NA-49')->firstOrFail()->na_head_id],
            ['name' => 'Zainab Malik', 'email' => 'teamleader4@example.com', 'team' => 'G-10 Khidmat Squad', 'reports_to' => fn () => Na::where('name', 'NA-49')->firstOrFail()->na_head_id],
            ['name' => 'Omar Farooqi', 'email' => 'teamleader5@example.com', 'team' => 'Bhara Kahu Khidmat Team', 'reports_to' => fn () => $ucHead->id],
        ];

        foreach ($teamLeaders as $data) {
            $team = $teams->firstWhere('name', $data['team']);

            $leader = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => 'password',
                'na_id' => $team->uc->na_id,
                'uc_id' => $team->uc_id,
                'department_id' => $team->department_id,
                'team_id' => $team->id,
                'reporting_head_id' => ($data['reports_to'])(),
            ]);
            $leader->assignRole('team_leader');
            $team->update(['leader_id' => $leader->id]);
        }

        $teams = $teams->fresh(); // pick up leader_id updates for reporting_head fallback below

        // Volunteers: spread across every staffed team in every NA so the
        // whole org chart has real people in it, not just NA-48.
        $staffedTeams = Team::whereNotNull('department_id')->orderBy('id')->get();

        $volunteerNames = [
            'Ali Hassan', 'Fatima Noor', 'Usman Ghani', 'Mariam Yousuf', 'Hamza Tariq',
            'Amna Shah', 'Zeeshan Iqbal', 'Rabia Sultan', 'Adeel Chaudhry', 'Nida Farooq',
            'Kashif Mehmood', 'Sadia Batool', 'Waqas Ahmed', 'Iqra Nawaz', 'Faisal Rashid',
            'Hira Younas', 'Junaid Malik', 'Mahnoor Siddiqi', 'Asad Bhatti', 'Sobia Rehman',
            'Danish Iqbal', 'Laiba Khalid', 'Shahzaib Qureshi', 'Noreen Akhtar',
        ];

        foreach ($volunteerNames as $i => $name) {
            $team = $staffedTeams->get($i % $staffedTeams->count());
            $na = Na::find($team->uc->na_id);

            $user = User::factory()->create([
                'name' => $name,
                'email' => 'volunteer'.($i + 1).'@example.com',
                'password' => 'password',
                'na_id' => $team->uc->na_id,
                'uc_id' => $team->uc_id,
                'department_id' => $team->department_id,
                'team_id' => $team->id,
                'reporting_head_id' => $team->leader_id ?? $na?->na_head_id ?? $ucHead->id,
            ]);
            $user->assignRole('user');
        }
    }
}
