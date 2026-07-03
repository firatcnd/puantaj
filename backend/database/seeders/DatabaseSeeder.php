<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // README'de paylaşılan test kullanıcısı
        User::firstOrCreate(
            ['email' => 'admin@puantaj.test'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );

        $this->call([
            LookupSeeder::class,
            PersonnelSeeder::class,
            TripSeeder::class,
            TimesheetSeeder::class,
        ]);
    }
}
