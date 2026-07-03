<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Personnel;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Personel Excel içe aktarımı.
 * Departman ve pozisyon İSİM ile eşleştirilir; her satır tek tek doğrulanır.
 * Hatalı satırlar atlanır ve rapor edilir (geçerli satırlar yine de eklenir).
 */
class PersonnelImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    /** @var array<int, string> */
    public array $errors = [];

    private Collection $departments;
    private Collection $positions;

    public function __construct()
    {
        // İsim -> model eşlemeleri (küçük harfe indirgenmiş anahtarla)
        $this->departments = Department::all()->keyBy(fn ($d) => mb_strtolower($d->name));
        $this->positions = Position::all()->keyBy(fn ($p) => mb_strtolower($p->name));
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2; // başlık satırı + 1 (kullanıcıya gösterilecek satır no)

            try {
                $this->importRow($row, $line);
            } catch (Throwable $e) {
                $this->errors[] = "Satır {$line}: beklenmeyen bir hata oluştu.";
            }
        }
    }

    private function importRow(Collection $row, int $line): void
    {
        $fullName = trim((string) $row->get('ad_soyad'));
        $registrationNo = trim((string) $row->get('sicil_no'));
        $departmentName = mb_strtolower(trim((string) $row->get('departman')));
        $positionName = mb_strtolower(trim((string) $row->get('pozisyon')));

        // Tamamen boş satırları sessizce atla
        if ($fullName === '' && $registrationNo === '') {
            return;
        }

        $department = $this->departments->get($departmentName);
        $position = $this->positions->get($positionName);

        $data = [
            'full_name' => $fullName,
            'registration_no' => $registrationNo,
            'department_id' => $department?->id,
            'position_id' => $position?->id,
            'hire_date' => $this->parseDate($row->get('ise_giris_tarihi')),
            'is_active' => $this->parseBool($row->get('durum')),
        ];

        $validator = Validator::make($data, [
            'full_name' => ['required', 'string', 'max:255'],
            'registration_no' => ['required', 'string', 'max:50', 'unique:personnel,registration_no'],
            'department_id' => ['required'],
            'position_id' => ['required'],
            'hire_date' => ['required', 'date'],
        ]);

        if (! $department && $departmentName !== '') {
            $validator->after(fn ($v) => $v->errors()->add('department_id', "'{$row->get('departman')}' departmanı bulunamadı."));
        }
        if (! $position && $positionName !== '') {
            $validator->after(fn ($v) => $v->errors()->add('position_id', "'{$row->get('pozisyon')}' pozisyonu bulunamadı."));
        }

        // Pozisyon seçilen departmana ait olmalı
        if ($department && $position && $position->department_id !== $department->id) {
            $validator->after(fn ($v) => $v->errors()->add('position_id', "'{$position->name}' pozisyonu '{$department->name}' departmanına ait değil."));
        }

        if ($validator->fails()) {
            $this->errors[] = "Satır {$line}: " . implode(' ', $validator->errors()->all());
            return;
        }

        Personnel::create($data);
        $this->imported++;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Excel'in sayısal tarih serisini de destekle
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function parseBool(mixed $value): bool
    {
        $normalized = mb_strtolower(trim((string) $value));

        return in_array($normalized, ['pasif', 'hayır', 'hayir', '0', 'false', 'no'], true) ? false : true;
    }
}
