<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRoleRequest extends StoreRoleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['name'] = [
            'required', 'string', 'max:100',
            Rule::unique('roles', 'name')
                ->ignore($this->route('role'))
                ->whereNull('deleted_at'),
        ];

        return $rules;
    }
}
