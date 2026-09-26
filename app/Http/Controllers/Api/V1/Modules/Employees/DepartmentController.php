<?php

namespace App\Http\Controllers\Api\V1\Modules\Employees;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Employees\DepartmentService;
use App\Http\Requests\Modules\Employees\DepartmentRequest;
use App\Http\Resources\Modules\Employees\DepartmentResource;

class DepartmentController extends Controller
{
    public function __construct(protected DepartmentService $departmentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->departmentService->index($request->all());

            return ApiResponse::success(DepartmentResource::collection($data));
        });
    }

    public function store(DepartmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->departmentService->store($request->validated());

            return ApiResponse::success(DepartmentResource::make($data), 'Department created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->departmentService->show($id, ['branch']);

            return ApiResponse::success(DepartmentResource::make($data));
        });
    }

    public function update(DepartmentRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->departmentService->update($id, $request->validated());

            return ApiResponse::success(DepartmentResource::make($data), 'Department updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->departmentService->destroy($id);

            return ApiResponse::success([], 'Department deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->departmentService->status($id);

            return ApiResponse::success(DepartmentResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->departmentService->list();

            return ApiResponse::success($data);
        });
    }
}
