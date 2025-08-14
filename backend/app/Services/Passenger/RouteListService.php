<?php
namespace App\Services\Passenger;

use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;

class RouteListService
{
    /**
     * Get all active routes for the current company with a summary of their trips.
     */
    public function getRoutesWithSummary(): Collection
    {
        // Eager load relationships and calculate aggregates directly from the database for best performance.
        $routes = Route::where('status', true)
            ->withCount([
                'trips as outbound_trip_count' => fn($query) => $query->where('direction', 'outbound')->where('is_active', true),
                'trips as inbound_trip_count' => fn($query) => $query->where('direction', 'inbound')->where('is_active', true),
            ])
            ->withMin('trips as first_trip_time', 'departure_time')
            ->withMax('trips as last_trip_time', 'departure_time')
            ->get();

        return $routes;
    }
}
