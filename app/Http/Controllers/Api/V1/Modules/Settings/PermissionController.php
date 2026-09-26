<?php

namespace App\Http\Controllers\Api\V1\Modules\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Modules\Settings\Role\PermissionResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Features\Settings\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(protected PermissionService $permissionService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->permissionService->index($request->all());

            return ApiResponse::success(PermissionResource::collection($data), 'Permissions retrieved successfully');
        });
    }

    public function show(string $id, Request $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->permissionService->show($id, $request->get('relations', []));

            return ApiResponse::success(PermissionResource::make($data), 'Permission retrieved successfully');
        });
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:permissions,title,'.$id],
        ]);

        return $this->handleRequest(function () use ($validated, $id) {
            $data = $this->permissionService->update($validated, $id);

            return ApiResponse::success(PermissionResource::make($data), 'Permission updated successfully');
        });
    }

    public function getList(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->permissionService->getList($request->all());

            return ApiResponse::success($data);
        });
    }
}
