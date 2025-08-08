<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\DriverStoreRequest;
use App\Http\Resources\DriverResource;
use App\Services\DriverService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\FileUploadTrait;

class DriverController extends BaseController
{
    use FileUploadTrait;

    protected UserService $userService;
    protected DriverService $driverService;

    public function __construct(UserService $userService, DriverService $driverService)
    {
       $this->userService = $userService;
       $this->driverService = $driverService;

        // Prottekta method-er jonno alada alada permission check kora hocche
        // Ete security aro beshi shoktishali hoy
        $this->middleware('can:view drivers')->only(['index', 'show']);
        $this->middleware('can:create drivers')->only(['store']);
        $this->middleware('can:edit drivers')->only(['update']);
        $this->middleware('can:delete drivers')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $perPage = $request->get('per_page', 15);

        $drivers = $this->driverService->getAll(['user'], $perPage);
        if ($drivers->isEmpty()) {
            return response_error('No drivers found.', [], 404);
        }

        return DriverResource::collection($drivers);
        } catch (\Exception $e) {
            return response_error('Failed to fetch drivers.', ['error' => $e->getMessage()], 500);
        }

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
    public function store(DriverStoreRequest $request)
    {
        try{
            $validatedData = $request->validated();

        $userData = [
            'company_id' => tenant('id'),
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['pin_code']), // by default, using pin_code as password
            'phone_number' => $validatedData['phone_number'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $imagePath = $this->handleFileUpload($request, 'avatar', 'avatars');
            if ($imagePath) {
                $userData['avatar'] = $imagePath;
            }
        }

        $transactionalCallback = function ($user) use ($validatedData) {

            $user->assignRole('Driver');

            $driverData = [
                'company_id' => tenant('id'),
                'staff_number' => $validatedData['staff_number'],
                'pin_code' => Hash::make($validatedData['pin_code']),
                'license_number' => $validatedData['license_number'],
                'license_expiry_date' => $validatedData['license_expiry_date'],
            ];


            $user->driver()->create($driverData);
        };


        $user = $this->userService->create($userData, [], $transactionalCallback);

        $user->load('driver');
        return response_success('Driver created successfully.', $user);

        } catch (\Exception $e) {
            return response_error('Failed to create driver.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $driver = $this->driverService->getById($id, ['user']);

        if (!$driver) {
            return response_error('Driver not found.', [], 404);
        }
        return new DriverResource($driver);
        }catch (\Exception $e) {
            return response_error('Failed to fetch driver.', ['error' => $e->getMessage()], 500);
        }
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
        try{
            $driver = $this->driverService->getById($id, ['user:id,avatar']);
            $user = $driver->user;
          //remove avatar if exists
        if (!$driver) {
            return response_error('Driver not found.', [], 404);
        }

        if ($user->avatar) {
            $this->deleteFile($user->getRawOriginal('avatar'));
        }
        //delete user and driver
        $user->delete();
        // $driver->delete();

        return response_success('Driver deleted successfully.');
        } catch (\Exception $e) {
            return response_error('Failed to delete driver.', ['error' => $e->getMessage()], 500);
        }
    }
}
