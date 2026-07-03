<?php

namespace App\Exports;

use App\Models\Personnel;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PersonnelExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters)
    {
    }

    public function query(): Builder
    {
        return Personnel::with(['department', 'position'])
            ->filter($this->filters)
            ->orderBy('full_name');
    }

    public function headings(): array
    {
        return ['Ad Soyad', 'Sicil No', 'Departman', 'Pozisyon', 'İşe Giriş Tarihi', 'Durum'];
    }

    public function map($personnel): array
    {
        return [
            $personnel->full_name,
            $personnel->registration_no,
            $personnel->department->name,
            $personnel->position->name,
            $personnel->hire_date->format('d.m.Y'),
            $personnel->is_active ? 'Aktif' : 'Pasif',
        ];
    }
}
