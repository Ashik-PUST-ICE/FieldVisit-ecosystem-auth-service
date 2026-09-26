<?php

namespace App\Http\Controllers\Api\V1\Modules\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Clients\PhoneBookRequest;
use App\Http\Resources\Modules\Clients\PhoneBookResource;
use App\Models\User;
use App\Services\Applications\Api\ApiResponse;
use App\Services\ClientPortal\PhoneBookService;
use Illuminate\Http\Request;

class PhoneBookController extends Controller
{
    public function __construct(protected PhoneBookService $phoneBookService) {}

    public function index(Request $request, string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $user = User::findOrFail($id);
            $user->load(['phoneBooks']);

            return ApiResponse::success(['phone_books' => PhoneBookResource::collection($user->phoneBooks)], 'All phone books retrieved successfully');
        });
    }

    public function store(PhoneBookRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->phoneBookService->store($id, $request->validated()['phone_books']);

            return ApiResponse::success(PhoneBookResource::collection($data), 'Phone books created successfully', 201);
        });
    }

    public function show(Request $request, string $id, string $phoneBookId)
    {
        return $this->handleRequest(function () use ($id, $phoneBookId) {
            $user = User::findOrFail($id);
            $data = $user->phoneBooks()->findOrFail($phoneBookId);

            return ApiResponse::success(PhoneBookResource::make($data), 'Phone book retrieved successfully');
        });
    }

    public function update(PhoneBookRequest $request, string $id, string $phoneBookId)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $result = $this->phoneBookService->update($id, $request->validated()['phone_books']);

            return ApiResponse::success(PhoneBookResource::collection($result), 'Phone books updated successfully', 200);
        });
    }

    public function destroy(Request $request, string $id, string $phoneBookId)
    {
        return $this->handleRequest(function () use ($id, $phoneBookId) {
            $result = $this->phoneBookService->destroy($id, [$phoneBookId]);
            $user = User::findOrFail($id);
            $user->load(['phoneBooks']);
            $remainingPhoneBooks = $user->phoneBooks;

            return ApiResponse::success(['deleted' => PhoneBookResource::collection(collect($result)), 'remaining_phone_books' => PhoneBookResource::collection($remainingPhoneBooks)], 'Phone book entry deleted successfully');
        });
    }
}
