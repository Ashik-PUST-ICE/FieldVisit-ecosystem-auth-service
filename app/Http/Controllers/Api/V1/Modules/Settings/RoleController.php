<?php

namespace App\Http\Controllers\Api\V1\Modules\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Settings\Role\RoleRequest;
use App\Http\Resources\Modules\Settings\Role\RoleResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Features\Settings\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roleService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->roleService->index($request->all());

            return ApiResponse::success(RoleResource::collection($data), 'Roles retrieved successfully');
        });
    }

    public function store(RoleRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->roleService->store($request->validated());

            return ApiResponse::success(RoleResource::make($data), 'Role created successfully');
        });
    }

    public function show(string $id, Request $request)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->roleService->show($id, ['permissions']);

            return ApiResponse::success(RoleResource::make($data), 'Role retrieved successfully');
        });
    }

    public function update(RoleRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->roleService->update($request->validated(), $id);

            return ApiResponse::success(RoleResource::make($data), 'Role updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->roleService->destroy($id);

            return ApiResponse::success(null, 'Role deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $role = $this->roleService->toggleStatus($id);

            return ApiResponse::success(RoleResource::make($role), 'Role status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->roleService->list();

            return ApiResponse::success($data, 'Roles list retrieved successfully');
        });
    }

    public function assignPermissions(string $id, Request $request)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        return $this->handleRequest(function () use ($id, $request) {
            $this->roleService->assignPermissionToRole($id, $request->get('permissions', []));

            return ApiResponse::success(null, 'Permissions assigned successfully');
        });
    }
}
