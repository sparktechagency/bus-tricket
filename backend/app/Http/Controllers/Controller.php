<?php


namespace App\Http\Controllers;

// Import the base controller class from Laravel
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    // By extending BaseController, this class now has access to
    // methods like middleware(), authorize(), etc.
}
