<?php

namespace App\Http\Resources;

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
            'trip' => $this->trip,
            'google_map_link' => $this->google_map_link,
            'status' => $this->status,
            //calculate duration based on stops first and last departure times
            'duration' => $this->stops->isNotEmpty() ? \Carbon\Carbon::parse($this->stops->first()->departure_time)->diffInMinutes(\Carbon\Carbon::parse($this->stops->last()->departure_time)) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // nested relations
            'stops' => RouteStopResource::collection($this->whenLoaded('stops')),
            'fares' => FareResource::collection($this->whenLoaded('fares')),
        ];
    }
}
