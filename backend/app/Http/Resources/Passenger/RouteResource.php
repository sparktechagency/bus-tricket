<?php

namespace App\Http\Resources\Passenger;

use Carbon\Carbon;
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
            'inbound' => count($this->stops),
            'outbound' => count($this->stops),
            'trip' => $this->trip,
            'status' => $this->status,
            'first_trip' => $this->stops->isNotEmpty()
                ? Carbon::parse($this->stops->first()->departure_time)->format('h : i a')
                : null,
            'last_trip' => $this->stops->isNotEmpty()
                ? Carbon::parse($this->stops->last()->departure_time)->format('h : i a')
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
