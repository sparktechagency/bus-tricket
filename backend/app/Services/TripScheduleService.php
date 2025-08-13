<?php

namespace App\Services;

use App\Models\Trip;
use App\Services\BaseService;
use Carbon\Carbon;

class TripScheduleService
{

    public function getCalculatedScheduleForTrip(int $tripId): array
    {

        $trip = Trip::with(['route.stops' => function ($query) {
            $query->orderBy('stop_order');
        }])->findOrFail($tripId);
        $departureTime = Carbon::parse($trip->departure_time);
        $schedule = [];
        foreach ($trip->route->stops as $stop) {
            $arrivalTime = $departureTime->copy()->addMinutes($stop->minutes_from_start);

            $schedule[] = [
                'stop_name' => $stop->location_name,
                'arrival_time' => $arrivalTime->format('h:i A'),
            ];
        }
        return [
            'trip_id' => $trip->id,
            'route_id' => $trip->route->id,
            'company_id' => $trip->company_id,
            'route_name' => $trip->route->name,
            'trip_departure_time' => Carbon::parse($trip->departure_time)->format('h:i A'),
            'schedule' => $schedule,
        ];
    }

}
