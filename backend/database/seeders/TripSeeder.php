<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $positions = Position::pluck('id', 'name');

        $trips = [
            ['İstanbul - Ankara', 'IST-ANK', 'İstanbul', 'Ankara', ['Şoför' => 600, 'Host' => 350, 'Muavin' => 300]],
            ['İstanbul - Bursa', 'IST-BUR', 'İstanbul', 'Bursa', ['Şoför' => 450, 'Host' => 275, 'Muavin' => 225]],
            ['İstanbul - İzmir', 'IST-IZM', 'İstanbul', 'İzmir', ['Şoför' => 700, 'Host' => 400, 'Muavin' => 340]],
            ['Ankara - Antalya', 'ANK-ANT', 'Ankara', 'Antalya', ['Şoför' => 650, 'Host' => 380, 'Muavin' => 320]],
            ['İzmir - Antalya', 'IZM-ANT', 'İzmir', 'Antalya', ['Şoför' => 550, 'Host' => 330, 'Muavin' => 280]],
            // Bilinçli olarak Muavin ücreti tanımlanmamış sefer: "ücret tanımlı değil"
            // iş kuralının ürettiği anlamlı hata mesajını göstermek için.
            ['İstanbul - Trabzon', 'IST-TRA', 'İstanbul', 'Trabzon', ['Şoför' => 900, 'Host' => 500]],
        ];

        foreach ($trips as [$name, $code, $departure, $arrival, $rates]) {
            $trip = Trip::create([
                'name' => $name,
                'code' => $code,
                'departure_point' => $departure,
                'arrival_point' => $arrival,
                'is_active' => true,
            ]);

            foreach ($rates as $positionName => $rate) {
                $trip->rates()->create([
                    'position_id' => $positions[$positionName],
                    'rate' => $rate,
                ]);
            }
        }
    }
}
