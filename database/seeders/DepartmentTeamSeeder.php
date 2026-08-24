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
        // (Fundraising below has teams in UC F-10, UC F-11, and UC G-9).
        $departments = collect(['Fundraising', 'Hospital', 'Mosque', 'Khidmat', 'Dawah', 'Administration'])
            ->mapWithKeys(fn (string $name) => [$name => Department::create(['name' => $name])]);

        // This organization operates within Islamabad only. Islamabad
        // Capital Territory has no Provincial Assembly (it's a federal
        // territory, not part of any province), so NA -> UC is the real
        // top-level structure here: NA is the unit a person is actually
        // assigned to manage (its "NA Head"), and every UC underneath is
        // their responsibility — NA-48 below has two UCs precisely to
        // demonstrate that. "sector" (F-10, F-11, G-9) is kept only as an
        // optional, informal label on the UC.
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
                'UC F-11' => [
                    'sector' => 'F-11',
                    'teams' => [],
                ],
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
