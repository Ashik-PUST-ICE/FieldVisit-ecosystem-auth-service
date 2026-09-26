<?php

namespace App\Http\Controllers\Api\Microservices;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Microservices\UserService;
use App\Services\Applications\Api\ApiResponse;
use App\Http\Resources\Microservices\Users\UserResource;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {

            $data = $this->userService->index($request->all());

            return ApiResponse::success(UserResource::collection($data));
        });
    }

    public function show(Request $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->userService->show($id);

            return ApiResponse::success(UserResource::make($data));
        });
    }
}
