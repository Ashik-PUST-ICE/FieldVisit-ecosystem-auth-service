<?php

namespace App\Http\Controllers\Api\V1\Modules\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Address\ZoneRequest;
use App\Http\Resources\Modules\Address\ZoneResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Address\ZoneService;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function __construct(protected ZoneService $zoneService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->zoneService->index($request->all());

            return ApiResponse::success(ZoneResource::collection($data));
        });
    }

    public function store(ZoneRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->zoneService->store($request->validated());

            return ApiResponse::success(ZoneResource::make($data), 'Zone created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->zoneService->show($id);

            return ApiResponse::success(ZoneResource::make($data));
        });
    }

    public function update(ZoneRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->zoneService->update($id, $request->validated());

            return ApiResponse::success(ZoneResource::make($data), 'Zone updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->zoneService->destroy($id);

            return ApiResponse::success([], 'Zone deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->zoneService->status($id);

            return ApiResponse::success(ZoneResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->zoneService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
