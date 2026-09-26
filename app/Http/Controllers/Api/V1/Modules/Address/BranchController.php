<?php

namespace App\Http\Controllers\Api\V1\Modules\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Address\BranchRequest;
use App\Http\Resources\Modules\Address\BranchResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Address\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(protected BranchService $BranchService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->BranchService->index($request->all());

            return ApiResponse::success(BranchResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(BranchRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->BranchService->store($request->validated());

            return ApiResponse::success(BranchResource::make($data), 'Branch Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->BranchService->show($id);

            return ApiResponse::success(BranchResource::make($data));
        });
    }

    public function update(string $id, BranchRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->BranchService->update($id, $request->validated());

            return ApiResponse::success(BranchResource::make($data), 'Branch Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->BranchService->destroy($id);

            return ApiResponse::success($data, 'Branch Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->BranchService->status($id);

            return ApiResponse::success(BranchResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->BranchService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
