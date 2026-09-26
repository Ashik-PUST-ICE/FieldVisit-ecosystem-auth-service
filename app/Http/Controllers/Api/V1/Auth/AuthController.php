<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

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

    public function me(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->authService->me($request->all());

            return ApiResponse::success($data, 'User retrieved successfully');
        });
    }

    public function logout(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $this->authService->logout($request->all());

            return ApiResponse::success(null, 'Logout successful');
        });
    }
}
