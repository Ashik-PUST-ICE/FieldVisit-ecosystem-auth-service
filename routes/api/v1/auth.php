<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Modules\Company\CompanyController;
use App\Http\Controllers\Api\V1\Modules\User\UserController;
use App\Http\Controllers\Api\V1\Services\ServiceController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth', 'middleware' => 'throttle:auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
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

Route::group(['middleware' => ['auth:api', 'verify.jwt', 'throttle:api']], function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');

    Route::prefix('settings')->group(function () {
        Route::get('roles/list', [\App\Http\Controllers\Api\V1\Modules\Settings\RoleController::class, 'list']);
        Route::apiResource('roles', \App\Http\Controllers\Api\V1\Modules\Settings\RoleController::class);
        Route::patch('roles/{id}/toggle-status', [\App\Http\Controllers\Api\V1\Modules\Settings\RoleController::class, 'toggleStatus']);
        Route::post('roles/{id}/permissions', [\App\Http\Controllers\Api\V1\Modules\Settings\RoleController::class, 'assignPermissions']);

        Route::get('permissions/list', [\App\Http\Controllers\Api\V1\Modules\Settings\PermissionController::class, 'getList']);
        Route::apiResource('permissions', \App\Http\Controllers\Api\V1\Modules\Settings\PermissionController::class)->only(['index', 'show', 'update']);

        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
