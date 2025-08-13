<?php

namespace App\Http\Controllers\Api\V1\Passenger;

use App\Http\Controllers\Controller;
use App\Http\Resources\Passenger\RouteResource;
use App\Models\Route;
use Illuminate\Http\Request;

class RoutesController extends Controller
{
    public function __construct()
    {
        // Middleware can be applied here if needed


    }
    //available Routes for passengers
    public function index(Request $request)
    {
        // Fetch routes based on the company_id from the request
        try{

        $routes = Route::with('stops')->where('status', true)->get();
        if( $routes->isEmpty()) {
            return response()->json(['message' => 'No active routes found'], 404);
        }

        return RouteResource::collection($routes);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching routes: ' . $e->getMessage()], 500);
        }

    }

    //show route details
    public function show($id)
    {
        // Fetch a specific route by ID
        $route = Route::with(['fares', 'stops'])->find($id);
        if (!$route) {
            return response()->json(['message' => 'Route not found'], 404);
        }
        return $route;
    }
}
