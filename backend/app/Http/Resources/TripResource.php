<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'departure_point' => $this->departure_point,
            'arrival_point' => $this->arrival_point,
            'is_active' => $this->is_active,
            'rates' => $this->whenLoaded('rates', fn () => $this->rates->map(fn ($rate) => [
                'id' => $rate->id,
                'position_id' => $rate->position_id,
                'position' => $rate->position->name,
                'rate' => (float) $rate->rate,
            ])),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
