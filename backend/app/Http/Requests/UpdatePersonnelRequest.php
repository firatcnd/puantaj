<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePersonnelRequest extends StorePersonnelRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['registration_no'] = [
            'required', 'string', 'max:50',
            Rule::unique('personnel', 'registration_no')
                ->ignore($this->route('personnel'))
                ->whereNull('deleted_at'),
        ];

        return $rules;
    }
}
