<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Operasyon', 'Filo Yönetimi', 'İnsan Kaynakları'] as $name) {
            Department::firstOrCreate(['name' => $name]);
        }

        foreach (['Şoför', 'Host', 'Muavin'] as $name) {
            Position::firstOrCreate(['name' => $name]);
        }
    }
}
