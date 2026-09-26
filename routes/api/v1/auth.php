<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Services\ServiceController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['prefix' => 'service'], function () {
    Route::post('/token', [ServiceController::class, 'generateToken']);
});

Route::get('/auth/public-key', function () {
    $path = storage_path('oauth-public.key');
    abort_unless(file_exists($path), 404, 'Public key not found');

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'public, max-age=600',
    ]);
});

Route::group(['middleware' => ['auth:api', 'verify.jwt']], function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
