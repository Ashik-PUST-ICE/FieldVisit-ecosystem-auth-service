<?php

use App\Http\Controllers\Api\Microservices\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'microservices'], function () {

    // get users
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
});
