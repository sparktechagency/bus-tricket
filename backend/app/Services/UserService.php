<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\User;

class UserService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = User::class;

    public function __construct()
    {
        parent::__construct();
    }
}
