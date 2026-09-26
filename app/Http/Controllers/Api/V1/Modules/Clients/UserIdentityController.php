<?php

namespace App\Http\Controllers\Api\V1\Modules\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UserIdentityRequest;
use App\Http\Resources\Client\UserIdentityResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\ClientPortal\UserIdentityService;
use App\Traits\FileUpload;
use Illuminate\Http\Request;

class UserIdentityController extends Controller
{
    use FileUpload;

    public function __construct(protected UserIdentityService $userIdentityService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->userIdentityService->index($request->all());

            return ApiResponse::success(UserIdentityResource::collection($data));
        });
    }

    // public function store(UserIdentityRequest $request)
    // {
    //     return $this->handleRequest(function () use ($request) {
    //         $data = $this->userIdentityService->store($request->validated());
    //         return ApiResponse::success(UserIdentityResource::make($data), 'User identity created successfully', 201);
    //     });
    // }

    public function store(UserIdentityRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validated();

            if ($request->hasFile('file')) {
                $validated['file'] = $this->uploadFile($request, 'file', $this->file_dir . 'documents/');
            }

            $data = $this->userIdentityService->store($validated);

            return ApiResponse::success(UserIdentityResource::make($data), 'User identity created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->userIdentityService->show($id);

            return ApiResponse::success(UserIdentityResource::make($data));
        });
    }

    // public function update(UserIdentityRequest $request, string $id)
    // {
    //     return $this->handleRequest(function () use ($request, $id) {
    //         $data = $this->userIdentityService->update($id, $request->validated());
    //         return ApiResponse::success(UserIdentityResource::make($data), 'User identity updated successfully');
    //     });
    // }

    public function update(UserIdentityRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validated();

            $document = $this->userIdentityService->show($id);

            if ($request->hasFile('file')) {
                $validated['file'] = $this->uploadFile($request, 'file', $this->file_dir . 'documents/', $document->file);
            }

            $data = $this->userIdentityService->update($id, $validated);

            return ApiResponse::success(UserIdentityResource::make($data), 'User identity updated successfully', 202);
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->userIdentityService->destroy($id);

            return ApiResponse::success([], 'User identity deleted successfully');
        });
    }
}
