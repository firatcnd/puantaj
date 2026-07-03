<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonnelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'registration_no' => $this->registration_no,
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn () => $this->department->name),
            'position_id' => $this->position_id,
            'position' => $this->whenLoaded('position', fn () => $this->position->name),
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
