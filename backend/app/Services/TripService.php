<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Trip;

class TripService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Trip::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    
}
