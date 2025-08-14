<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
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
            'company_id' => $this->company_id,
            'name' => $this->name,
            'route_prefix' => $this->route_prefix,
            'google_map_link' => $this->google_map_link,
            'status' => $this->status,
            'duration' => $this->whenLoaded('stops', function () {
                return $this->stops->last()?->minutes_from_start;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // nested relations
            'trips' => TripSummaryResource::collection($this->whenLoaded('trips')),
            'stops' => RouteStopResource::collection($this->whenLoaded('stops')),
            'fares' => FareResource::collection($this->whenLoaded('fares')),
        ];
    }
}
