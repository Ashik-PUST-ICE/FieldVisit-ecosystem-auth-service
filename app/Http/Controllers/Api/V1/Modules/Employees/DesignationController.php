<?php

namespace App\Http\Controllers\Api\V1\Modules\Employees;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Employees\DesignationService;
use App\Http\Requests\Modules\Employees\DesignationRequest;
use App\Http\Resources\Modules\Employees\DesignationResource;

class DesignationController extends Controller
{
    public function __construct(protected DesignationService $designationService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->designationService->index($request->all());

            return ApiResponse::success(DesignationResource::collection($data));
        });
    }

    public function store(DesignationRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->designationService->store($request->validated());

            return ApiResponse::success(DesignationResource::make($data), 'Designation created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->designationService->show($id);

            return ApiResponse::success(DesignationResource::make($data));
        });
    }

    public function update(DesignationRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->designationService->update($id, $request->validated());

            return ApiResponse::success(DesignationResource::make($data), 'Designation updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->designationService->destroy($id);

            return ApiResponse::success([], 'Designation deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->designationService->status($id);

            return ApiResponse::success(DesignationResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->designationService->list();

            return ApiResponse::success($data);
        });
    }
}
