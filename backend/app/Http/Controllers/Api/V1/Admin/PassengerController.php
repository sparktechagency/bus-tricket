<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePassengerProfile;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Admin\PassengerService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;

class PassengerController extends Controller
{
    use FileUploadTrait;

    protected PassengerService $passengerService;
    protected AuthService $authService;
    public function __construct(PassengerService $passengerService, AuthService $authService)
    {
        $this->passengerService = $passengerService;
        $this->authService = $authService;
        // Middleware for authorization

        $this->middleware('can:view passengers')->only(['index', 'show']);
        $this->middleware('can:create passengers')->only(['store']);
        $this->middleware('can:edit passengers')->only(['update']);
        $this->middleware('can:delete passengers')->only(['destroy']);
        $this->middleware('can:manage passenger wallet')->only(['topUpWallet']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $perPage = $request->get('per_page', 15);

            $queryCallback = function ($query) {
                $query->role('Passenger');
            };

            $passengers = $this->passengerService->getAll(['paymentMethods', 'wallet'], $perPage, $queryCallback);

            //create wallet for each passenger if not exists

            return UserResource::collection($passengers);
        } catch (\Exception $e) {
            return response_error('Failed to retrieve passengers: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegisterRequest $request)
    {
        try {

            $validatedData = $request->validated();
            $validatedData['username'] = $this->authService->generateUniqueUsername($validatedData['name']);
            $validatedData['email_verified_at'] = now();
            // Automatically verify email for admin-created passengers
            $passenger = $this->passengerService->create($validatedData);

            $passenger->assignRole('Passenger');

            //create wallet for the passenger
            $passenger->wallet()->create(['balance' => 0]);

            return response_success('Passenger created successfully.', $passenger, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating passenger: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $passenger = $this->passengerService->getById($id, ['paymentMethods', 'wallet']);
            if (!$passenger) {
                return response_error('Passenger not found.', [], 404);
            }
            return new UserResource($passenger);
        } catch (\Exception $e) {
            return response_error('Failed to retrieve passenger: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePassengerProfile $request, string $id)
    {
        $ValidatedData = $request->validated();
        try {
            $passenger = $this->passengerService->update((int)$id, $ValidatedData);
            return response_success('Passenger updated successfully.', new UserResource($passenger));
        } catch (\Exception $e) {
            return response_error('Failed to update passenger: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $passenger = $this->passengerService->getById($id, ['wallet', 'paymentMethods']);
            if (!$passenger) {
                return response_error('Passenger not found.', [], 404);
            }
            //check role passenger
            if (!$passenger->hasRole('Passenger')) {
                return response_error('This user is not a passenger.', [], 400);
            }

            //if exists avatar, delete it
            if ($passenger->avatar) {
                $this->deleteFile($passenger->avatar);
            }

            // Delete the passenger's wallet and payment methods
            $passenger->wallet()->delete();
            $passenger->paymentMethods()->delete();

            // Delete the passenger
            $passenger->delete();

            return response_success('Passenger deleted successfully.');
        } catch (\Exception $e) {
            return response_error('Failed to delete passenger: ' . $e->getMessage(), [], 500);
        }
    }

    //top up passenger wallet
    public function topUpWallet(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        try {
            $passenger = $this->passengerService->getById($id, ['wallet']);
            if (!$passenger || !$passenger->hasRole('Passenger')) {
                return response_error('Passenger not found.', [], 404);
            }

            // Check if the passenger has a wallet
            if (!$passenger->wallet) {
                return response_error('Passenger does not have a wallet.', [], 400);
            }

            // Top up the wallet
            $amount = $request->input('amount');
            $passenger->wallet->increment('balance', $amount);

            return response_success('Wallet topped up successfully.', ['balance' => $passenger->wallet->balance]);
        } catch (\Exception $e) {
            return response_error('Failed to top up wallet: ' . $e->getMessage(), [], 500);
        }
    }
}
