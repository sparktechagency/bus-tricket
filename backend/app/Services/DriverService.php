<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\User;
use App\Services\BaseService;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverService extends BaseService
{
    use FileUploadTrait;

    public function __construct()
    {
        parent::__construct(new Driver());
    }



    /**
     * Updates an existing Driver and their associated User record.
     */
    // public function updateDriver(int $driverId, array $data): Driver
    // {
    //     $driver = $this->getById($driverId);
    //     $user = $driver->user;

    //     DB::transaction(function () use ($driver, $user, $data) {
    //         // Update User table
    //         if (isset($data['name'])) $user->name = $data['name'];
    //         if (isset($data['email'])) $user->email = $data['email'];
    //         $user->save();

    //         // Update Driver table
    //         if (isset($data['staff_number'])) $driver->staff_number = $data['staff_number'];
    //         if (isset($data['license_number'])) $driver->license_number = $data['license_number'];

    //         if (isset($data['profile_picture'])) {
    //             $this->deleteFile($driver->avatar);
    //             $driver->avatar = $this->handleFileUpload(request(), 'avatar', 'drivers');
    //         }

    //         $driver->save();
    //     });

    //     return $driver->load('user');
    // }

    // /**
    //  * Deletes a Driver and their associated User record.
    //  */
    // public function deleteDriver(int $driverId): bool
    // {
    //     $driver = $this->getById($driverId);

    //     return DB::transaction(function () use ($driver) {
    //         $this->deleteFile($driver->profile_picture_path);
    //         return $driver->delete();
    //     });
    // }
}
