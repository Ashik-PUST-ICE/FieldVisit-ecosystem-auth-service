<?php

namespace App\Http\Controllers\Api\V1\Modules\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Address\SubzoneRequest;
use App\Http\Resources\Modules\Address\SubzoneResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Address\SubzoneService;
use Illuminate\Http\Request;

class SubzoneController extends Controller
{
    public function __construct(protected SubzoneService $subzoneService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->subzoneService->index($request->all());

            return ApiResponse::success(SubzoneResource::collection($data));
        });
    }

    public function store(SubzoneRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->subzoneService->store($request->validated());

            return ApiResponse::success(SubzoneResource::make($data), 'Subzone created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->subzoneService->show($id);

            return ApiResponse::success(SubzoneResource::make($data));
        });
    }

    public function update(SubzoneRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->subzoneService->update($id, $request->validated());

            return ApiResponse::success(SubzoneResource::make($data), 'Subzone updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->subzoneService->destroy($id);

            return ApiResponse::success([], 'Subzone deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->subzoneService->status($id);

            return ApiResponse::success(SubzoneResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->subzoneService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
