<?php

use App\Http\Controllers\Api\V1\Admin\CompanyController;
use App\Http\Controllers\Api\V1\Admin\DriverController;
use App\Http\Controllers\Api\V1\Admin\RouteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\Passenger\PaymentController;
use App\Http\Controllers\Api\V1\Passenger\RoutesController;
use App\Http\Controllers\Api\V1\WebhookController;

// --- Public Routes (Authentication) ---
Route::middleware('identify.company')->prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

        Route::post('/verify', [VerificationController::class, 'verify'])->name('api.v1.auth.verify');
        Route::post('/resend-verification', [VerificationController::class, 'resendVerification'])->name('api.v1.auth.resendVerification');

        Route::post('/forgot-password', [PasswordController::class, 'forgotPassword'])->name('api.v1.auth.forgotPassword');
        Route::post('/verify-password-otp', [PasswordController::class, 'verifyResetOtp'])->name('api.v1.auth.verifyResetOtp');
        Route::post('/reset-password-with-token', [PasswordController::class, 'resetPasswordWithToken'])->name('api.v1.auth.resetPasswordWithToken');

        //driver login
        Route::post('/driver/login', [\App\Http\Controllers\Api\V1\Driver\AuthController::class, 'login'])->name('api.v1.driver.auth.login');
    });
});

// --- Protected Routes (User must be logged in) ---
Route::middleware('auth:sanctum', 'identify.company')->prefix('v1')->group(function () {

    // Auth related protected routes
    Route::prefix('auth')->name('api.v1.auth.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/update-password', [PasswordController::class, 'updatePassword'])->name('updatePassword');
    });

    // Profile related protected routes
    Route::prefix('profile')->name('api.v1.profile.')->group(function () {
        Route::get('/me', [ProfileController::class, 'me'])->name('me');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
    });

    // --- Admin Panel Routes ---
    Route::prefix('admin')->name('api.v1.admin.')->group(function () {
        //driver management routes
        Route::apiResource('drivers', DriverController::class)->except(['create', 'edit']);
        //company management routes
        Route::apiResource('companies', CompanyController::class)->except(['create', 'edit'])->withoutMiddleware('identify.company');
        //route management routes
        Route::apiResource('routes', RouteController::class)->except(['create', 'edit']);
        //fare management routes
    });

    //--- Passenger Mobile App Routes ---
    Route::prefix('passenger')->name('api.v1.passenger.')->group(function () {
        // Transaction history
        Route::get('/payment/transactions', [PaymentController::class, 'getTransactionHistory'])->name('transactions.history');
        Route::post('/payment/create-card-setup-session', [PaymentController::class, 'createCardSetupSession']);
        // Route::post('/payment/top-up', [PaymentController::class, 'topUpWithSavedCard']);
        // Route::post('/payment/top-up', [PaymentController::class, 'createPaymentIntent']);
        Route::post('/payment/top-up', [PaymentController::class, 'createPaymentSession']);
        Route::post('/payment/refund', [PaymentController::class, 'requestRefund']);

        // Routes for passengers
        Route::get('/routes', [RoutesController::class, 'index'])->name('routes.index');
        Route::get('/routes/{id}', [RoutesController::class, 'show'])->name('routes.show');
    });
});

// --- Webhook Routes ---
Route::post('/v1/stripe/webhook', [WebhookController::class, 'handleStripeWebhook']);

