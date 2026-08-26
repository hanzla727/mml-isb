<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Na;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Departments are a shared, org-wide list — the same Fundraising,
        // Hospital, Mosque, ... categories exist everywhere. Every
        // volunteer/head belongs to one directly (via User::department_id),
        // independent of which NA/UC they're in.
        collect(['Fundraising', 'Hospital', 'Mosque', 'Khidmat', 'Dawah', 'Administration'])
            ->each(fn (string $name) => Department::create(['name' => $name]));

        // This organization operates within Islamabad only. Islamabad
        // Capital Territory has no Provincial Assembly (it's a federal
        // territory, not part of any province), so NA -> UC is the real
        // top-level structure here: NA is the unit a person is actually
        // assigned to manage (its "NA Head"), and every UC underneath is
        // their responsibility. Islamabad currently has three National
        // Assembly seats — NA-48, NA-49 and NA-50 below — each carrying its
        // real UCs: the planned urban sectors (E/F/G/H/I) for NA-48/NA-49,
        // and Islamabad's rural Union Councils for NA-50. "sector" (e.g.
        // "F-10") is kept only as an optional, informal label on the UC.
        $structure = [
            'NA-48' => ['UC F-10', 'UC F-11', 'UC F-6', 'UC F-7', 'UC F-8', 'UC G-6', 'UC G-7'],
            'NA-49' => ['UC G-9', 'UC G-10', 'UC G-11', 'UC I-8', 'UC I-9', 'UC I-10', 'UC Bahria Town', 'UC DHA Islamabad'],
            // NA-50 covers Islamabad's rural Union Councils.
            'NA-50' => ['UC Bhara Kahu', 'UC Tarnol', 'UC Nilore', 'UC Sihala', 'UC Tarlai', 'UC Humak'],
        ];

        foreach ($structure as $naName => $ucNames) {
            $na = Na::create(['name' => $naName, 'status' => 'active']);

            foreach ($ucNames as $ucName) {
                $sector = str_starts_with($ucName, 'UC ') && preg_match('/^UC ([A-Z]-\d+)$/', $ucName, $m) ? $m[1] : null;

                $na->ucs()->create([
                    'name' => $ucName,
                    'sector' => $sector,
                    'status' => 'active',
                ]);
            }
        }
    }
}
