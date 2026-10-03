<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StorageSettingsRequest;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Features\Settings\StorageSettingsService;

class StorageSettingsController extends Controller
{
    public function __construct(protected StorageSettingsService $storageSettingsService) {}

    public function show()
    {
        return $this->handleRequest(function () {
            return ApiResponse::success(
                $this->storageSettingsService->show(),
                'Storage settings retrieved successfully'
            );
        });
    }

    public function update(StorageSettingsRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            return ApiResponse::success(
                $this->storageSettingsService->update($request->validated()),
                'Storage settings updated successfully'
            );
        });
    }

    public function test()
    {
        return $this->handleRequest(function () {
            return ApiResponse::success(null, $this->storageSettingsService->test());
        });
    }
}
