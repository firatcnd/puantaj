<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\Trip;
use App\Services\TimesheetService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimesheetSeeder extends Seeder
{
    public function __construct(private readonly TimesheetService $service)
    {
    }

    public function run(): void
    {
        $personnel = Personnel::active()->with('position')->get();
        $trips = Trip::with('rates')->get();
        $now = now();

        // Son 3 ay için puantaj üret (dashboard grafikleri dolu görünsün diye)
        foreach ([2, 1, 0] as $monthsAgo) {
            $period = $now->copy()->subMonthsNoOverflow($monthsAgo);

            foreach ($personnel as $person) {
                // Her ay her personele değil; gerçekçi olsun diye ~%75'ine puantaj aç
                if (random_int(1, 4) === 1) {
                    continue;
                }

                $this->service->create(
                    $this->headerData($person->id, $period),
                    $this->randomEntries($person->position_id, $trips, $period)
                );
            }
        }
    }

    private function headerData(int $personnelId, Carbon $period): array
    {
        $daysInMonth = $period->daysInMonth;
        $weekendDays = 8;
        $leaveDays = random_int(0, 2);
        $sickDays = random_int(0, 1);
        $publicHolidayDays = random_int(0, 2);
        $workDays = $daysInMonth - $weekendDays - $leaveDays - $sickDays - $publicHolidayDays;

        return [
            'personnel_id' => $personnelId,
            'year' => $period->year,
            'month' => $period->month,
            'work_days' => $workDays,
            'leave_days' => $leaveDays,
            'sick_days' => $sickDays,
            'public_holiday_days' => $publicHolidayDays,
            'weekend_days' => $weekendDays,
            'overtime_hours' => random_int(0, 20),
            'undertime_hours' => random_int(0, 4),
            'description' => null,
        ];
    }

    private function randomEntries(int $positionId, $trips, Carbon $period): array
    {
        // Yalnızca personelin pozisyonuna ücret tanımlı seferlerden seç
        $eligible = $trips->filter(
            fn (Trip $trip) => $trip->rateForPosition($positionId) !== null
        )->values();

        return $eligible->random(min(random_int(1, 3), $eligible->count()))
            ->map(fn (Trip $trip) => [
                'trip_id' => $trip->id,
                'duty_date' => $period->copy()->day(random_int(1, $period->daysInMonth))->format('Y-m-d'),
                'trip_count' => random_int(2, 10),
            ])
            ->all();
    }
}
