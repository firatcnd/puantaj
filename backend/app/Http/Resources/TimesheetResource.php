<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'personnel_id' => $this->personnel_id,
            'personnel' => $this->whenLoaded('personnel', fn () => [
                'id' => $this->personnel->id,
                'full_name' => $this->personnel->full_name,
                'registration_no' => $this->personnel->registration_no,
                'department' => $this->personnel->department?->name,
            ]),
            'position_id' => $this->position_id,
            'position' => $this->whenLoaded('position', fn () => $this->position->name),
            'year' => $this->year,
            'month' => $this->month,
            'work_days' => $this->work_days,
            'leave_days' => $this->leave_days,
            'sick_days' => $this->sick_days,
            'public_holiday_days' => $this->public_holiday_days,
            'weekend_days' => $this->weekend_days,
            'overtime_hours' => (float) $this->overtime_hours,
            'undertime_hours' => (float) $this->undertime_hours,
            'description' => $this->description,
            'total_amount' => (float) $this->total_amount,
            'entries_count' => $this->whenCounted('entries'),
            'entries_sum_trip_count' => $this->whenAggregated('entries', 'trip_count', 'sum', fn ($value) => (int) $value),
            'entries' => TimesheetEntryResource::collection($this->whenLoaded('entries')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
