<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LookupSeeder::class,
            PersonnelSeeder::class,
            TripSeeder::class,
            TimesheetSeeder::class,
        ]);
    }
}
