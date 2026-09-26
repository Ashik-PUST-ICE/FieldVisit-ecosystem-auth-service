<?php

use App\Http\Controllers\Api\V1\Modules\Address\BranchController;
use App\Http\Controllers\Api\V1\Modules\Address\CityController;
use App\Http\Controllers\Api\V1\Modules\Address\CountryController;
use App\Http\Controllers\Api\V1\Modules\Address\StateController;
use App\Http\Controllers\Api\V1\Modules\Address\SubzoneController;
use App\Http\Controllers\Api\V1\Modules\Address\ZoneController;
use App\Http\Controllers\Api\V1\Modules\Settings\PermissionController;
use App\Http\Controllers\Api\V1\Modules\Settings\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => 'auth:api'],
    function () {
        Route::group(['prefix' => 'settings'], function () {
            // roles
            Route::get('roles/list', [RoleController::class, 'list']);
            Route::apiResource('roles', RoleController::class);
            Route::patch('roles/{id}/toggle-status', [RoleController::class, 'toggleStatus']);
            Route::post('roles/{id}/permissions', [RoleController::class, 'assignPermissions']);

            // permissions
            Route::get('permissions/list', [PermissionController::class, 'getList']);
            Route::apiResource('permissions', PermissionController::class)->only(['index', 'show', 'update']);

            Route::prefix('address')->group(function () {
                Route::patch('countries/{id}/status', [CountryController::class, 'status']);
                Route::get('countries/list', [CountryController::class, 'list']);
                Route::apiResource('countries', CountryController::class);
                Route::patch('states/{id}/status', [StateController::class, 'status']);
                Route::get('states/list', [StateController::class, 'list']);
                Route::apiResource('states', StateController::class);
                Route::patch('cities/{id}/status', [CityController::class, 'status']);
                Route::get('cities/list', [CityController::class, 'list']);
                Route::apiResource('cities', CityController::class);

                Route::patch('branches/{id}/status', [BranchController::class, 'status']);
                Route::get('branches/list', [BranchController::class, 'list']);
                Route::apiResource('branches', BranchController::class);
                Route::patch('zones/{id}/status', [ZoneController::class, 'status']);
                Route::get('zones/list', [ZoneController::class, 'list']);
                Route::apiResource('zones', ZoneController::class);
                Route::patch('sub-zones/{id}/status', [SubzoneController::class, 'status']);
                Route::get('sub-zones/list', [SubzoneController::class, 'list']);
                Route::apiResource('sub-zones', SubzoneController::class);
            });
        });
    }
);
