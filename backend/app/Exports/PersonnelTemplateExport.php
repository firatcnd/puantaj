<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** Toplu personel içe aktarımı için örnek doldurulmuş şablon. */
class PersonnelTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function headings(): array
    {
        return ['Ad Soyad', 'Sicil No', 'Departman', 'Pozisyon', 'İşe Giriş Tarihi', 'Durum'];
    }

    public function array(): array
    {
        return [
            ['Ahmet Yılmaz', 'P1001', 'Operasyon', 'Şoför', '2023-05-15', 'Aktif'],
            ['Ayşe Demir', 'P1002', 'Operasyon', 'Host', '2024-01-10', 'Aktif'],
        ];
    }
}
