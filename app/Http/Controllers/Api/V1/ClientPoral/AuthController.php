<?php

namespace App\Http\Controllers\Api\V1\ClientPoral;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientPortal\Auth\LoginRequest;
use App\Services\Applications\Api\ApiResponse;
use App\Services\ClientPortal\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->authService->login($request->validated());

            return ApiResponse::success($data, 'Login successful');
        });
    }
}
