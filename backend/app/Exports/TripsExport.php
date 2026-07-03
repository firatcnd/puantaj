<?php

namespace App\Exports;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TripsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters)
    {
    }

    public function query(): Builder
    {
        return Trip::with('rates.position')
            ->filter($this->filters)
            ->orderBy('name');
    }

    public function headings(): array
    {
        return ['Sefer Adı', 'Sefer Kodu', 'Kalkış', 'Varış', 'Mesai Ücretleri', 'Durum'];
    }

    public function map($trip): array
    {
        $rates = $trip->rates
            ->map(fn ($rate) => "{$rate->position->name}: " . number_format($rate->rate, 2, ',', '.') . ' TL')
            ->join(' | ');

        return [
            $trip->name,
            $trip->code,
            $trip->departure_point,
            $trip->arrival_point,
            $rates,
            $trip->is_active ? 'Aktif' : 'Pasif',
        ];
    }
}
