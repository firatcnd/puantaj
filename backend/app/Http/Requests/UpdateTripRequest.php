<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateTripRequest extends StoreTripRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['code'] = [
            'required', 'string', 'max:50',
            Rule::unique('trips', 'code')
                ->ignore($this->route('trip'))
                ->whereNull('deleted_at'),
        ];

        return $rules;
    }
}
