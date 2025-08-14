<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status ? 'Active' : 'Inactive',
            'duration_in_minutes' => $this->stops->max('minutes_from_start') ?? 0,
            'trips' => TripSummaryResource::collection($this->whenLoaded('trips')),
        ];
    }
}
