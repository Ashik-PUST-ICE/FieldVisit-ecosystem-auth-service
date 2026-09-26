<?php

use App\Http\Controllers\Api\V1\Commons\FileUploadController;
use App\Http\Controllers\Api\V1\Webhooks\NagadController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'webhooks'], function () {
    Route::post('/nagad', [NagadController::class, 'handleNagadWebhook']);
});

Route::group(['prefix' => 'commons'], function () {
    Route::post('/upload-files', [FileUploadController::class, 'handleFileUpload']);
    Route::post('/delete-files', [FileUploadController::class, 'handleFileDelete']);
});
