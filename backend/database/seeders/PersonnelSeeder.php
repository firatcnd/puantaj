<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Personnel;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create(config('app.faker_locale'));

        $operasyon = Department::where('name', 'Operasyon')->first();
        $positions = Position::pluck('id', 'name');

        // Pozisyon dağılımı: seferler ağırlıklı olarak şoför/host/muavin ile döner.
        $distribution = [
            'Şoför' => 6,
            'Host' => 5,
            'Muavin' => 4,
        ];

        $counter = 1;

        foreach ($distribution as $positionName => $count) {
            for ($i = 0; $i < $count; $i++) {
                Personnel::create([
                    'full_name' => $faker->firstName() . ' ' . $faker->lastName(),
                    'registration_no' => sprintf('P%04d', $counter++),
                    'department_id' => $operasyon->id,
                    'position_id' => $positions[$positionName],
                    'hire_date' => $faker->dateTimeBetween('-8 years', '-3 months')->format('Y-m-d'),
                    'is_active' => $i === $count - 1 ? false : true, // her pozisyondan bir pasif örnek
                ]);
            }
        }
    }
}
