<?php

namespace App\Http\Requests;

use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'registration_no' => [
                'required', 'string', 'max:50',
                Rule::unique('personnel', 'registration_no')->whereNull('deleted_at'),
            ],
            'department_id' => ['required', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'position_id' => ['required', Rule::exists('positions', 'id')->whereNull('deleted_at')],
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // Pozisyon, seçilen departmana ait olmalıdır (örn. İnsan Kaynakları + Muavin olamaz).
        $validator->after(function (Validator $v) {
            $position = Position::find($this->input('position_id'));

            if ($position && (int) $position->department_id !== (int) $this->input('department_id')) {
                $v->errors()->add(
                    'position_id',
                    "\"{$position->name}\" pozisyonu seçilen departmana ait değil."
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'ad soyad',
            'registration_no' => 'sicil no',
            'department_id' => 'departman',
            'position_id' => 'pozisyon',
            'hire_date' => 'işe giriş tarihi',
            'is_active' => 'durum',
        ];
    }
}
