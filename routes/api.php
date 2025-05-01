<?php

use App\Http\Controllers\ConnectedUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::any('/register', [UserApiController::class, 'register']);
Route::post('/login', [UserApiController::class, 'login']);
Route::post('/verify-otp', [UserApiController::class, 'verifyOTP']);
Route::post('/resend-otp', [UserApiController::class, 'resendOTP']);

Route::post('/forgot-password', [UserApiController::class, 'forgotPassword']);
Route::post('/reset-password', [UserApiController::class, 'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/owners/users/{user}', [UserApiController::class, 'update']);

    Route::get('/owners/{owner}/connected-users', [ConnectedUserController::class, 'index']);
    Route::post('/owners/{owner}/connected-users', [UserApiController::class, 'register']);
    Route::get('/owners/{owner}/connected-users/{connectedUser}', [ConnectedUserController::class, 'show']);
    Route::post('/owners/{owner}/connected-users/{connectedUser}', [ConnectedUserController::class, 'update']);
    Route::delete('/owners/{owner}/connected-users/{connectedUser}', [ConnectedUserController::class, 'destroy']);

    Route::get('orders/{user}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'storeOrUpdate']);
});
