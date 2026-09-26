<?php

namespace App\Http\Controllers\Api\V1\Modules\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Address\CountryRequest;
use App\Http\Resources\Modules\Address\CountryResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Address\CountryService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->countryService->index($request->all());

            return ApiResponse::success(CountryResource::collection($data));
        });
    }

    public function store(CountryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->countryService->store($request->validated());

            return ApiResponse::success(CountryResource::make($data), 'Country created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->countryService->show($id);

            return ApiResponse::success(CountryResource::make($data));
        });
    }

    public function update(CountryRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->countryService->update($id, $request->validated());

            return ApiResponse::success(CountryResource::make($data), 'Country updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->countryService->destroy($id);

            return ApiResponse::success([], 'Country deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->countryService->status($id);

            return ApiResponse::success(CountryResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->countryService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
