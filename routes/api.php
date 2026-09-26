<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    require __DIR__ . '/api/v1/auth.php';
    require __DIR__ . '/api/v1/api.php';
    require __DIR__ . '/api/v1/microservices.php';
    require __DIR__ . '/api/v1/modules/settings.php';
});
