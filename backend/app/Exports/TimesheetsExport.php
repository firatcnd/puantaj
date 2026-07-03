<?php

namespace App\Exports;

use App\Models\Timesheet;
use App\Support\Months;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TimesheetsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters)
    {
    }

    public function query(): Builder
    {
        return Timesheet::with(['personnel.department', 'position'])
            ->withSum('entries', 'trip_count')
            ->filter($this->filters)
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Personel', 'Sicil No', 'Departman', 'Pozisyon', 'Ay', 'Yıl',
            'Çalışma Günü', 'İzin Günü', 'Rapor Günü', 'Resmi Tatil', 'Hafta Tatili',
            'Fazla Mesai (Saat)', 'Eksik Mesai (Saat)', 'Toplam Sefer',
            'Toplam Mesai Tutarı (TL)', 'Oluşturulma Tarihi',
        ];
    }

    public function map($timesheet): array
    {
        return [
            $timesheet->personnel->full_name,
            $timesheet->personnel->registration_no,
            $timesheet->personnel->department->name,
            $timesheet->position->name,
            Months::name($timesheet->month),
            $timesheet->year,
            $timesheet->work_days,
            $timesheet->leave_days,
            $timesheet->sick_days,
            $timesheet->public_holiday_days,
            $timesheet->weekend_days,
            (float) $timesheet->overtime_hours,
            (float) $timesheet->undertime_hours,
            (int) $timesheet->entries_sum_trip_count,
            (float) $timesheet->total_amount,
            $timesheet->created_at->format('d.m.Y H:i'),
        ];
    }
}
