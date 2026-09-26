<?php

use App\Http\Controllers\Api\V1\Modules\Settings\PermissionController;
use App\Http\Controllers\Api\V1\Modules\Settings\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => 'auth:api'],
    function () {
        Route::group(['prefix' => 'settings'], function () {
            Route::get('roles/list', [RoleController::class, 'list']);
            Route::apiResource('roles', RoleController::class);
            Route::patch('roles/{id}/toggle-status', [RoleController::class, 'toggleStatus']);
            Route::post('roles/{id}/permissions', [RoleController::class, 'assignPermissions']);

            Route::get('permissions/list', [PermissionController::class, 'getList']);
            Route::apiResource('permissions', PermissionController::class)->only(['index', 'show', 'update']);
        });
    }
);
