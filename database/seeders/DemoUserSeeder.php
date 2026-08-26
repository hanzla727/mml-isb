<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Na;
use App\Models\Uc;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Every seeded user gets a username (for app login) and a PIN. The PIN
     * is deliberately the same "1234" for everyone — this is demo data, and
     * a single memorable PIN means anyone can log in as any seeded account
     * without having to look one up per person.
     */
    private function usernameFor(string $name): string
    {
        return strtolower(str_replace(' ', '.', $name));
    }

    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Ahmad Raza',
            'email' => 'superadmin@example.com',
            'username' => $this->usernameFor('Ahmad Raza'),
            'password' => 'password',
            'pin' => '1234',
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
                'username' => $this->usernameFor($data['name']),
                'password' => 'password',
                'pin' => '1234',
            ]);
            $admin->assignRole('admin');
            $admin->adminNas()->attach(collect($data['nas'])->pluck('id'));
        }

        // NA Head: scoped to exactly one NA (na_id, no single uc_id — every
        // UC under the NA is their responsibility), and that NA points back
        // at them (na_head_id). NA-50 is deliberately left without a
        // dedicated NA Head — it's run by Admin + its UC Head instead,
        // showing that's a valid, real configuration too.
        $naHeads = [
            ['name' => 'Tariq Mehmood', 'email' => 'nahead1@example.com', 'na' => $na48],
            ['name' => 'Farah Naz', 'email' => 'nahead2@example.com', 'na' => $na49],
        ];

        foreach ($naHeads as $data) {
            $naHead = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $this->usernameFor($data['name']),
                'password' => 'password',
                'pin' => '1234',
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
            'username' => $this->usernameFor('Sarah Iqbal'),
            'password' => 'password',
            'pin' => '1234',
            'na_id' => $na50->id,
        ]);
        $ucHead->assignRole('uc_head');
        $ucHead->ucsHeaded()->attach([$ucBharaKahu->id, $ucTarnol->id]);

        // Volunteers: each placed in a specific (UC, Department) pair — a
        // few UCs are left with no volunteers on purpose, to show an NA/UC
        // can exist with nobody assigned there yet. reporting_head_id
        // resolves to whichever head actually oversees that placement: the
        // UC Head if they cover that UC, otherwise the NA Head for that NA,
        // otherwise the Admin responsible for it.
        $departments = Department::pluck('id', 'name');
        $ucs = Uc::pluck('id', 'name');

        $placements = [
            ['uc' => 'UC F-10', 'department' => 'Fundraising'],
            ['uc' => 'UC F-10', 'department' => 'Hospital'],
            ['uc' => 'UC F-10', 'department' => 'Mosque'],
            ['uc' => 'UC F-6', 'department' => 'Fundraising'],
            ['uc' => 'UC F-8', 'department' => 'Hospital'],
            ['uc' => 'UC G-6', 'department' => 'Mosque'],
            ['uc' => 'UC G-9', 'department' => 'Khidmat'],
            ['uc' => 'UC G-9', 'department' => 'Dawah'],
            ['uc' => 'UC G-9', 'department' => 'Administration'],
            ['uc' => 'UC G-9', 'department' => 'Fundraising'],
            ['uc' => 'UC G-10', 'department' => 'Khidmat'],
            ['uc' => 'UC I-8', 'department' => 'Dawah'],
            ['uc' => 'UC I-9', 'department' => 'Administration'],
            ['uc' => 'UC Bahria Town', 'department' => 'Fundraising'],
            ['uc' => 'UC Bhara Kahu', 'department' => 'Khidmat'],
            ['uc' => 'UC Tarnol', 'department' => 'Hospital'],
            ['uc' => 'UC Sihala', 'department' => 'Mosque'],
            ['uc' => 'UC Humak', 'department' => 'Fundraising'],
        ];

        $volunteerNames = [
            'Ali Hassan', 'Fatima Noor', 'Usman Ghani', 'Mariam Yousuf', 'Hamza Tariq',
            'Amna Shah', 'Zeeshan Iqbal', 'Rabia Sultan', 'Adeel Chaudhry', 'Nida Farooq',
            'Kashif Mehmood', 'Sadia Batool', 'Waqas Ahmed', 'Iqra Nawaz', 'Faisal Rashid',
            'Hira Younas', 'Junaid Malik', 'Mahnoor Siddiqi', 'Asad Bhatti', 'Sobia Rehman',
            'Danish Iqbal', 'Laiba Khalid', 'Shahzaib Qureshi', 'Noreen Akhtar',
            'Bilal Chaudhry', 'Ayesha Siddiqui', 'Hassan Raza', 'Zainab Malik', 'Omar Farooqi',
        ];

        foreach ($volunteerNames as $i => $name) {
            $placement = $placements[$i % count($placements)];
            $ucId = $ucs[$placement['uc']];
            $uc = Uc::find($ucId);

            $user = User::factory()->create([
                'name' => $name,
                'email' => 'volunteer'.($i + 1).'@example.com',
                'username' => $this->usernameFor($name),
                'password' => 'password',
                'pin' => '1234',
                'na_id' => $uc->na_id,
                'uc_id' => $ucId,
                'department_id' => $departments[$placement['department']],
                'reporting_head_id' => $this->reportingHeadFor($uc, $ucHead, $na48, $na49),
            ]);
            $user->assignRole('user');
        }
    }

    private function reportingHeadFor(Uc $uc, User $ucHead, Na $na48, Na $na49): ?int
    {
        if ($ucHead->ucsHeaded()->where('ucs.id', $uc->id)->exists()) {
            return $ucHead->id;
        }

        if ($uc->na_id === $na48->id) {
            return $na48->na_head_id;
        }

        if ($uc->na_id === $na49->id) {
            return $na49->na_head_id;
        }

        return null;
    }
}
