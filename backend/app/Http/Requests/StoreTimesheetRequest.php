<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTimesheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personnel_id' => ['required', Rule::exists('personnel', 'id')->whereNull('deleted_at')],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => [
                'required', 'integer', 'between:1,12',
                // Aynı personel için aynı ay içerisinde yalnızca bir puantaj oluşturulabilir.
                Rule::unique('timesheets', 'month')
                    ->where('personnel_id', $this->input('personnel_id'))
                    ->where('year', $this->input('year'))
                    ->whereNull('deleted_at'),
            ],
            // Gün alanları negatif olamaz.
            'work_days' => ['required', 'integer', 'min:0', 'max:31'],
            'leave_days' => ['required', 'integer', 'min:0', 'max:31'],
            'sick_days' => ['required', 'integer', 'min:0', 'max:31'],
            'public_holiday_days' => ['required', 'integer', 'min:0', 'max:31'],
            'weekend_days' => ['required', 'integer', 'min:0', 'max:31'],
            // Fazla / eksik mesai negatif olamaz.
            'overtime_hours' => ['required', 'numeric', 'min:0', 'max:744'],
            'undertime_hours' => ['required', 'numeric', 'min:0', 'max:744'],
            'description' => ['nullable', 'string', 'max:2000'],

            'entries' => ['present', 'array'],
            'entries.*.trip_id' => ['required', Rule::exists('trips', 'id')->whereNull('deleted_at')],
            'entries.*.duty_date' => ['required', 'date'],
            'entries.*.trip_count' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $year = (int) $this->input('year');
            $month = (int) $this->input('month');

            if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
                return; // temel kurallar zaten hata üretti
            }

            $daysInMonth = Carbon::create($year, $month)->daysInMonth;

            // Toplam gün sayısı ilgili ayın toplam gün sayısını geçemez.
            $totalDays = (int) $this->input('work_days')
                + (int) $this->input('leave_days')
                + (int) $this->input('sick_days')
                + (int) $this->input('public_holiday_days')
                + (int) $this->input('weekend_days');

            if ($totalDays > $daysInMonth) {
                $v->errors()->add(
                    'work_days',
                    "Girilen gün alanlarının toplamı ({$totalDays}) seçilen ayın gün sayısını ({$daysInMonth}) geçemez."
                );
            }

            // Görev tarihleri seçilen ay/yıl içinde olmalıdır.
            foreach ((array) $this->input('entries', []) as $index => $entry) {
                if (empty($entry['duty_date'])) {
                    continue;
                }

                $date = Carbon::parse($entry['duty_date']);

                if ($date->year !== $year || $date->month !== $month) {
                    $v->errors()->add(
                        "entries.{$index}.duty_date",
                        'Görev tarihi, puantajın ait olduğu ay içinde olmalıdır.'
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'personnel_id' => 'personel',
            'year' => 'yıl',
            'month' => 'ay',
            'work_days' => 'çalışma günü',
            'leave_days' => 'izin günü',
            'sick_days' => 'rapor günü',
            'public_holiday_days' => 'resmi tatil',
            'weekend_days' => 'hafta tatili',
            'overtime_hours' => 'fazla mesai',
            'undertime_hours' => 'eksik mesai',
            'description' => 'açıklama',
            'entries' => 'sefer kayıtları',
            'entries.*.trip_id' => 'sefer',
            'entries.*.duty_date' => 'görev tarihi',
            'entries.*.trip_count' => 'sefer adedi',
        ];
    }

    public function messages(): array
    {
        return [
            'month.unique' => 'Bu personel için seçilen ay ve yıla ait bir puantaj zaten mevcut.',
        ];
    }
}
