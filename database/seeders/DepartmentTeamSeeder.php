<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Na;
use Illuminate\Database\Seeder;

class DepartmentTeamSeeder extends Seeder
{
    public function run(): void
    {
        // Departments are a shared, org-wide list — the same Fundraising,
        // Hospital, Mosque, ... categories exist everywhere. A Department is
        // never scoped to an NA/UC; Team is what actually belongs to one UC,
        // and the same Department can have teams in several UCs/NAs at once
        // (Fundraising below has teams in several UCs across all three NAs).
        $departments = collect(['Fundraising', 'Hospital', 'Mosque', 'Khidmat', 'Dawah', 'Administration'])
            ->mapWithKeys(fn (string $name) => [$name => Department::create(['name' => $name])]);

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
        // Not every UC has teams yet — several are left empty on purpose to
        // demonstrate that an NA/UC can exist with no volunteers assigned.
        $structure = [
            'NA-48' => [
                'UC F-10' => [
                    'sector' => 'F-10',
                    'teams' => [
                        'Fundraising' => ['Donor Relations Team', 'Events Team'],
                        'Hospital' => ['Hospital Volunteers Team'],
                        'Mosque' => ['Mosque Committee Team'],
                    ],
                ],
                // No teams yet — still part of NA-48's territory, showing an
                // NA can have a UC with no active volunteers assigned yet.
                'UC F-11' => ['sector' => 'F-11', 'teams' => []],
                'UC F-6' => [
                    'sector' => 'F-6',
                    'teams' => ['Fundraising' => ['F-6 Fundraising Circle']],
                ],
                'UC F-7' => ['sector' => 'F-7', 'teams' => []],
                'UC F-8' => [
                    'sector' => 'F-8',
                    'teams' => ['Hospital' => ['F-8 Hospital Outreach Team']],
                ],
                'UC G-6' => [
                    'sector' => 'G-6',
                    'teams' => ['Mosque' => ['G-6 Mosque Volunteers']],
                ],
                'UC G-7' => ['sector' => 'G-7', 'teams' => []],
            ],
            'NA-49' => [
                'UC G-9' => [
                    'sector' => 'G-9',
                    'teams' => [
                        'Khidmat' => ['Khidmat Team'],
                        'Dawah' => ['Dawah Team'],
                        'Administration' => ['Admin Support Team'],
                        // Same Fundraising department as UC F-10 above —
                        // demonstrates a department reused across UCs/NAs.
                        'Fundraising' => ['Community Fundraising Team'],
                    ],
                ],
                'UC G-10' => [
                    'sector' => 'G-10',
                    'teams' => ['Khidmat' => ['G-10 Khidmat Squad']],
                ],
                'UC G-11' => ['sector' => 'G-11', 'teams' => []],
                'UC I-8' => [
                    'sector' => 'I-8',
                    'teams' => ['Dawah' => ['I-8 Dawah Circle']],
                ],
                'UC I-9' => [
                    'sector' => 'I-9',
                    'teams' => ['Administration' => ['I-9 Admin Support']],
                ],
                'UC I-10' => ['sector' => 'I-10', 'teams' => []],
                'UC Bahria Town' => [
                    'sector' => null,
                    'teams' => ['Fundraising' => ['Bahria Town Fundraising Team']],
                ],
                'UC DHA Islamabad' => ['sector' => null, 'teams' => []],
            ],
            // NA-50 covers Islamabad's rural Union Councils — no teams yet
            // in most of them, a realistic snapshot of where the
            // organization is still building a presence.
            'NA-50' => [
                'UC Bhara Kahu' => [
                    'sector' => null,
                    'teams' => ['Khidmat' => ['Bhara Kahu Khidmat Team']],
                ],
                'UC Tarnol' => [
                    'sector' => null,
                    'teams' => ['Hospital' => ['Tarnol Health Team']],
                ],
                'UC Nilore' => ['sector' => null, 'teams' => []],
                'UC Sihala' => [
                    'sector' => null,
                    'teams' => ['Mosque' => ['Sihala Mosque Committee']],
                ],
                'UC Tarlai' => ['sector' => null, 'teams' => []],
                'UC Humak' => [
                    'sector' => null,
                    'teams' => ['Fundraising' => ['Humak Fundraising Team']],
                ],
            ],
        ];

        foreach ($structure as $naName => $ucs) {
            $na = Na::create([
                'name' => $naName,
                'status' => 'active',
            ]);

            foreach ($ucs as $ucName => $ucData) {
                $uc = $na->ucs()->create([
                    'name' => $ucName,
                    'sector' => $ucData['sector'],
                    'status' => 'active',
                ]);

                foreach ($ucData['teams'] as $departmentName => $teamNames) {
                    foreach ($teamNames as $teamName) {
                        $departments[$departmentName]->teams()->create([
                            'uc_id' => $uc->id,
                            'name' => $teamName,
                        ]);
                    }
                }
            }
        }
    }
}
