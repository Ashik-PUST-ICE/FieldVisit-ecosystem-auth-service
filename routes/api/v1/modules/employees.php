<?php

use App\Http\Controllers\Api\V1\Modules\Employees\EmployeeController;
use App\Http\Controllers\Api\V1\Modules\Employees\DepartmentController;
use App\Http\Controllers\Api\V1\Modules\Employees\DesignationController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['verify.jwt']], function () {
    Route::get('employees/list', [EmployeeController::class, 'list']);
    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{id}/status', [EmployeeController::class, 'status']);

    Route::get('departments/list', [DepartmentController::class, 'list']);
    Route::apiResource('departments', DepartmentController::class);
    Route::patch('departments/{id}/status', [DepartmentController::class, 'status']);

    Route::get('designations/list', [DesignationController::class, 'list']);
    Route::apiResource('designations', DesignationController::class);
    Route::patch('designations/{id}/status', [DesignationController::class, 'status']);
});
