<?php

namespace App\Http\Controllers\Api\V1\Modules\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Address\CityRequest;
use App\Http\Resources\Modules\Address\CityResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Address\CityService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct(protected CityService $cityService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->cityService->index($request->all());

            return ApiResponse::success(CityResource::collection($data));
        });
    }

    public function store(CityRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->cityService->store($request->validated());

            return ApiResponse::success(CityResource::make($data), 'City created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->cityService->show($id);

            return ApiResponse::success(CityResource::make($data));
        });
    }

    public function update(CityRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->cityService->update($id, $request->validated());

            return ApiResponse::success(CityResource::make($data), 'City updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->cityService->destroy($id);

            return ApiResponse::success([], 'City deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->cityService->status($id);

            return ApiResponse::success(CityResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->cityService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
