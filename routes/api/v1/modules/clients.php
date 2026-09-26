<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Address\AddressBookController;
use App\Http\Controllers\Api\V1\AssignType\AssignTypeController;
use App\Http\Controllers\Api\V1\Modules\Clients\ClientController;
use App\Http\Controllers\Api\V1\Modules\Clients\PhoneBookController;
use App\Http\Controllers\Api\V1\Modules\Clients\UserGroupController;

Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['verify.jwt']], function () {
        Route::patch('user-groups/{id}/status', [UserGroupController::class, 'toggleStatus']);
        Route::get('user-groups/list', [UserGroupController::class, 'getList']);

        Route::apiResource('user-groups', UserGroupController::class);

        Route::get('clients/search', [ClientController::class, 'search']);
        Route::get('clients/list/without-connection', [ClientController::class, 'getClientsWithoutConnection']);
        Route::get('clients/get-clients', [ClientController::class, 'getClientType']);
        Route::get('clients/get-infos/{userId}/{networkId}', [ClientController::class, 'getClientInformation']);
        Route::apiResource('clients', ClientController::class);



        Route::post('send-otp', [ClientController::class, 'sendOtp']);
        Route::post('verify-otp', [ClientController::class, 'verifyOtp']);

        Route::prefix('clients/{id}')->group(function () {
            Route::put('/user-info', [ClientController::class, 'updateUser']);
            Route::put('/address-info', [ClientController::class, 'updateAddress']);
            Route::put('/identity-info', [ClientController::class, 'updateIdentity']);
            Route::put('/phone-books-info', [ClientController::class, 'updatePhoneBooks']);
            Route::apiResource('phone-books', PhoneBookController::class);

            Route::apiResource('addresses', AddressBookController::class);
        });

        Route::get('/get-assign-type/{type}', [AssignTypeController::class, 'index']);
    });
});
