<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('trips', 'code')->whereNull('deleted_at'),
            ],
            'departure_point' => ['required', 'string', 'max:255'],
            'arrival_point' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],

            // Bir sefer için en az bir pozisyona ücret tanımlanmalıdır (iş kuralı).
            'rates' => ['required', 'array', 'min:1'],
            'rates.*.position_id' => [
                'required', 'distinct', // aynı pozisyona ikinci kez ücret tanımlanamaz
                Rule::exists('positions', 'id')->whereNull('deleted_at'),
            ],
            'rates.*.rate' => ['required', 'numeric', 'min:0', 'max:999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'sefer adı',
            'code' => 'sefer kodu',
            'departure_point' => 'kalkış noktası',
            'arrival_point' => 'varış noktası',
            'is_active' => 'durum',
            'rates' => 'mesai ücretleri',
            'rates.*.position_id' => 'pozisyon',
            'rates.*.rate' => 'mesai ücreti',
        ];
    }

    public function messages(): array
    {
        return [
            'rates.required' => 'Bir sefer için en az bir pozisyona mesai ücreti tanımlanmalıdır.',
            'rates.min' => 'Bir sefer için en az bir pozisyona mesai ücreti tanımlanmalıdır.',
            'rates.*.position_id.distinct' => 'Aynı pozisyon için birden fazla ücret tanımlanamaz.',
        ];
    }
}
