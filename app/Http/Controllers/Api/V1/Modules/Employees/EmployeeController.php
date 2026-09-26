<?php

namespace App\Http\Controllers\Api\V1\Modules\Employees;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Employees\EmployeeService;
use App\Http\Requests\Modules\Employees\EmployeeRequest;
use App\Http\Resources\Modules\Employees\EmployeeResource;

class EmployeeController extends Controller
{
    public function __construct(protected EmployeeService $employeeService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->employeeService->index($request->all());

            return ApiResponse::success(EmployeeResource::collection($data));
        });
    }

    public function store(EmployeeRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->employeeService->store($request->validated());

            return ApiResponse::success(EmployeeResource::make($data), 'Employee created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->employeeService->show($id, [
                'employee.department',
                'employee.designation',
                'roles',
            ]);

            return ApiResponse::success(EmployeeResource::make($data));
        });
    }

    public function update(EmployeeRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->employeeService->update($id, $request->validated());

            return ApiResponse::success(EmployeeResource::make($data), 'Employee updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->employeeService->destroy($id);

            return ApiResponse::success([], 'Employee deleted successfully');
        });
    }


    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->employeeService->status($id);

            return ApiResponse::success(EmployeeResource::make($data), 'Status toggled successfully');
        });
    }


    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->employeeService->list();

            return ApiResponse::success($data);
        });
    }
}
