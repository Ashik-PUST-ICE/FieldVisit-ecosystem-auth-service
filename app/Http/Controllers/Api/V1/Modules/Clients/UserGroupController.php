<?php

namespace App\Http\Controllers\Api\V1\Modules\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Clients\UserGroupRequest;
use App\Http\Resources\Modules\Clients\UserGroups\UserGroupResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Clients\UserGroupService;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function __construct(protected UserGroupService $userGroupService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->userGroupService->index($request->all());

            return ApiResponse::success(UserGroupResource::collection($data));
        });
    }

    public function store(UserGroupRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->userGroupService->store($request->validated());

            return ApiResponse::success(UserGroupResource::make($data), 'Group created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->userGroupService->show($id);

            return ApiResponse::success(UserGroupResource::make($data));
        });
    }

    public function update(UserGroupRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->userGroupService->update($id, $request->validated());

            return ApiResponse::success(UserGroupResource::make($data), 'Group updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->userGroupService->destroy($id);

            return ApiResponse::success([], 'Group deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->userGroupService->toggleStatus($id);

            return ApiResponse::success(UserGroupResource::make($data), 'Group status toggled successfully');
        });
    }

    public function getList()
    {
        return $this->handleRequest(function () {
            $data = $this->userGroupService->getList();

            return ApiResponse::success($data);
        });
    }
}
