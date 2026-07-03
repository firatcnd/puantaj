<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateTimesheetRequest extends StoreTimesheetRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['month'] = [
            'required', 'integer', 'between:1,12',
            Rule::unique('timesheets', 'month')
                ->where('personnel_id', $this->input('personnel_id'))
                ->where('year', $this->input('year'))
                ->whereNull('deleted_at')
                ->ignore($this->route('timesheet')),
        ];

        return $rules;
    }
}
