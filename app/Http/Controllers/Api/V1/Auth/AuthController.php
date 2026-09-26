<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ProfileUpdateRequest;
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

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $this->authService->forgotPassword($request->validated());

            return ApiResponse::success(null, 'Password reset link sent to your email');
        });
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $this->authService->changePassword($request->validated());

            return ApiResponse::success(null, 'Password changed successfully');
        });
    }

    public function updateProfile(ProfileUpdateRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->authService->updateProfile($request->validated(), $request->file('image'));

            return ApiResponse::success($data, 'Profile updated successfully');
        });
    }
}
