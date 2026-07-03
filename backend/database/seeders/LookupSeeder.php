<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /** Departmanlar ve her departmana ait pozisyonlar. */
    private const STRUCTURE = [
        'Operasyon' => ['Şoför', 'Host', 'Muavin'],
        'Filo Yönetimi' => ['Filo Sorumlusu', 'Bakım Teknisyeni'],
        'İnsan Kaynakları' => ['İK Uzmanı'],
    ];

    public function run(): void
    {
        foreach (self::STRUCTURE as $departmentName => $positions) {
            $department = Department::firstOrCreate(['name' => $departmentName]);

            foreach ($positions as $positionName) {
                Position::firstOrCreate(
                    ['name' => $positionName],
                    ['department_id' => $department->id]
                );
            }
        }
    }
}
