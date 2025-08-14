<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripSummaryResource extends JsonResource
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
            'departure_time' => \Carbon\Carbon::parse($this->departure_time)->format('H:i'),
            'direction' => $this->direction,
            'trip_number' => ($this->route->route_prefix ?? 'T') . str_pad($this->id, 3, '0', STR_PAD_LEFT),
        ];
    }
}
