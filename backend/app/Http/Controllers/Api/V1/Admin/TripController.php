<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TripStoreRequest;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected TripService $tripService;
    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
        //middleware for authorization
        $this->middleware('can:view trips')->only(['index', 'show']);
        $this->middleware('can:create trips')->only(['store']);
        $this->middleware('can:edit trips')->only(['update']);
        $this->middleware('can:delete trips')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TripStoreRequest $request)
    {
        try{
            $trip = $this->tripService->createTrip($request->validated());
            return response_success('Trip created successfully.', $trip, 201);
        } catch (\Exception $e) {
            return response_error('Failed to create trip: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
