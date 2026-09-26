<?php

use App\Http\Controllers\Api\V1\ClientPoral\AuthController;
use App\Http\Controllers\Api\V1\ClientPoral\DashboardController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'client-portal'], function () {

    // authentications
    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => ['auth:api']], function () {
        // dashboard
        Route::get('dashboard/summary', [DashboardController::class, 'summary']);
    });
});
