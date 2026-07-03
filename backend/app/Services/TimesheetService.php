<?php

namespace App\Services;

use App\Exceptions\MissingRateException;
use App\Models\Personnel;
use App\Models\Timesheet;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class TimesheetService
{
    /**
     * Puantaj oluşturur. Birim ücretler ve toplam tutar kullanıcıdan asla alınmaz;
     * personelin MEVCUT pozisyonu üzerinden sefer ücretleri sistemce belirlenir.
     *
     * @param array $data  Doğrulanmış puantaj başlık alanları
     * @param array $entries  [['trip_id' =>, 'duty_date' =>, 'trip_count' =>], ...]
     */
    public function create(array $data, array $entries): Timesheet
    {
        $personnel = Personnel::with('position')->findOrFail($data['personnel_id']);

        return DB::transaction(function () use ($data, $entries, $personnel) {
            $timesheet = Timesheet::create([
                ...$data,
                // Pozisyon snapshot'ı: personelin pozisyonu ileride değişse bile
                // bu puantaj oluşturulduğu andaki pozisyonla hesaplanmış kalır.
                'position_id' => $personnel->position_id,
                'total_amount' => 0,
            ]);

            $this->syncEntries($timesheet, $personnel, $entries);

            return $timesheet->load(['personnel.department', 'position', 'entries.trip']);
        });
    }

    public function update(Timesheet $timesheet, array $data, array $entries): Timesheet
    {
        $personnel = Personnel::with('position')->findOrFail($data['personnel_id']);

        return DB::transaction(function () use ($timesheet, $data, $entries, $personnel) {
            $timesheet->update([
                ...$data,
                'position_id' => $personnel->position_id,
            ]);

            $timesheet->entries()->delete();
            $this->syncEntries($timesheet, $personnel, $entries);

            return $timesheet->load(['personnel.department', 'position', 'entries.trip']);
        });
    }

    /**
     * Sefer satırlarını yazar; her satır için birim ücreti pozisyona göre bulur,
     * satır ve genel toplamı hesaplayıp puantaja işler.
     *
     * @throws MissingRateException ilgili pozisyona ücret tanımlı değilse
     */
    private function syncEntries(Timesheet $timesheet, Personnel $personnel, array $entries): void
    {
        $trips = Trip::with('rates')
            ->whereIn('id', array_column($entries, 'trip_id'))
            ->get()
            ->keyBy('id');

        $total = 0;

        foreach ($entries as $entry) {
            $trip = $trips[$entry['trip_id']];
            $rate = $trip->rateForPosition($personnel->position_id);

            if ($rate === null) {
                throw new MissingRateException($trip->name, $personnel->position->name);
            }

            $lineTotal = round($rate->rate * $entry['trip_count'], 2);

            $timesheet->entries()->create([
                'trip_id' => $trip->id,
                'duty_date' => $entry['duty_date'],
                'trip_count' => $entry['trip_count'],
                'unit_rate' => $rate->rate,
                'line_total' => $lineTotal,
            ]);

            $total += $lineTotal;
        }

        $timesheet->update(['total_amount' => $total]);
    }
}
