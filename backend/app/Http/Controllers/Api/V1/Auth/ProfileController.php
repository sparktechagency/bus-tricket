<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles', 'permissions');
        return response_success('User data fetched successfully.', $user);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $this->authService->updateProfile(
                $request->user(),
                $request->only('name', 'email')
            );
            return response_success('Profile updated successfully.', $user);
        } catch (ValidationException $e) {
            return response_error('Validation failed.', $e->errors(), 422);
        }
    }
}
