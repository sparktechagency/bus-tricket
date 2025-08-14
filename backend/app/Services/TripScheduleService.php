<?php
namespace App\Services;

use App\Models\Trip;
use Carbon\Carbon;

class TripScheduleService
{
    /**
     * Calculates the arrival time for each stop of a given trip,
     * respecting the trip's direction.
     */
    public function getCalculatedScheduleForTrip(int $tripId): array
    {
        $trip = Trip::with(['route.stops' => fn($q) => $q->orderBy('stop_order')])->findOrFail($tripId);

        $departureTime = Carbon::parse($trip->departure_time);
        $stops = $trip->route->stops;


        if ($trip->direction === 'inbound') {
            $stops = $this->reverseStops($stops);
        }

        $schedule = [];
        foreach ($stops as $stop) {
            $arrivalTime = $departureTime->copy()->addMinutes($stop['minutes_from_start']);
            $schedule[] = [
                'stop_name' => $stop['location_name'],
                'arrival_time' => $arrivalTime->format('h:i A'),
            ];
        }

        return [
            'route_name' => $trip->route->name,
            'trip_departure_time' => $departureTime->format('h:i A'),
            'direction' => $trip->direction,
            'schedule' => $schedule,
        ];
    }

    /**
     * Reverses the order of stops and recalculates their timings for inbound trips.
     */
    private function reverseStops($stops)
    {
        $reversedStops = $stops->reverse()->values();
        $totalDuration = $stops->max('minutes_from_start');

        return $reversedStops->map(function ($stop, $index) use ($totalDuration) {
            return [
                'location_name' => $stop->location_name,
                'minutes_from_start' => $totalDuration - $stop->minutes_from_start,
            ];
        });
    }
}
