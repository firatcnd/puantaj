<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('roles', 'name')->whereNull('deleted_at'),
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', Rule::in(Role::PAGES)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'rol adı',
            'permissions' => 'sayfa izinleri',
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'Rol için en az bir sayfa izni seçilmelidir.',
            'permissions.min' => 'Rol için en az bir sayfa izni seçilmelidir.',
        ];
    }
}
