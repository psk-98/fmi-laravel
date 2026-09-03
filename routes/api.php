<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->name('api.v1')->group(function () {
    Route::post('auth/login', LoginController::class)->middleware('throttle:login')->name('auth.login');
    Route::post('auth/register', RegisterUserController::class)->name('auth.register');
});
