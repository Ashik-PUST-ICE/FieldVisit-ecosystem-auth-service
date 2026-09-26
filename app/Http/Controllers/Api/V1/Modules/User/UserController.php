<?php

namespace App\Http\Controllers\Api\V1\Modules\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Modules\User\UserResource;
use App\Http\Requests\Modules\User\UserRequest;
use App\Models\User;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\User\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $users = $this->userService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($users, 'Users retrieved successfully');
        });
    }

    public function store(UserRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $user = $this->userService->store($request->validated());

            return ApiResponse::success(new UserResource($user), 'User created successfully', 201);
        });
    }

    public function show(User $user)
    {
        return $this->handleRequest(function () use ($user) {
            $user = $this->userService->show($user);

            return ApiResponse::success(new UserResource($user), 'User retrieved successfully');
        });
    }

    public function update(UserRequest $request, User $user)
    {
        return $this->handleRequest(function () use ($request, $user) {
            $user = $this->userService->update($user, $request->validated());

            return ApiResponse::success(new UserResource($user), 'User updated successfully');
        });
    }

    public function toggleStatus(User $user)
    {
        return $this->handleRequest(function () use ($user) {
            $user = $this->userService->toggleStatus($user);

            return ApiResponse::success(new UserResource($user), 'User status updated successfully');
        });
    }
}
