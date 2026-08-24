<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Model events are intentionally left
     * enabled (no WithoutModelEvents) — DemoDataSeeder relies on real
     * Eloquent hooks: DailyReport's total_hours auto-calculation and every
     * model's LogsActivity audit trail both fire off model events.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DepartmentTeamSeeder::class,
            DemoUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
