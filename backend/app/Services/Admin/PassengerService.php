<?php

namespace App\Services\Admin;

use App\Services\BaseService;
use App\Models\User as Passenger;

class PassengerService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Passenger::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }
}
