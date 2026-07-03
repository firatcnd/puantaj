<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'trip' => $this->whenLoaded('trip', fn () => [
                'id' => $this->trip->id,
                'name' => $this->trip->name,
                'code' => $this->trip->code,
            ]),
            'duty_date' => $this->duty_date?->format('Y-m-d'),
            'trip_count' => $this->trip_count,
            'unit_rate' => (float) $this->unit_rate,
            'line_total' => (float) $this->line_total,
        ];
    }
}
