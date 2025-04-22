<?php

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
