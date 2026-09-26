<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\ServiceRequest;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Features\ServiceListService;

class ServiceController extends Controller
{
    public function __construct(protected ServiceListService $serviceListService) {}

    public function generateToken(ServiceRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->serviceListService->generateToken($request->validated());

            return ApiResponse::success($data, 'Token generated successfully');
        });
    }
}
