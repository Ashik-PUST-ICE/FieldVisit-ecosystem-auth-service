<?php

namespace App\Http\Controllers\Api\V1\Modules\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Address\StateRequest;
use App\Http\Resources\Modules\Address\StateResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Address\StateService;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function __construct(protected StateService $stateService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stateService->index($request->all());

            return ApiResponse::success(StateResource::collection($data));
        });
    }

    public function store(StateRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stateService->store($request->validated());

            return ApiResponse::success(StateResource::make($data), 'State created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stateService->show($id);

            return ApiResponse::success(StateResource::make($data));
        });
    }

    public function update(StateRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->stateService->update($id, $request->validated());

            return ApiResponse::success(StateResource::make($data), 'State updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->stateService->destroy($id);

            return ApiResponse::success([], 'State deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stateService->status($id);

            return ApiResponse::success(StateResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stateService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
