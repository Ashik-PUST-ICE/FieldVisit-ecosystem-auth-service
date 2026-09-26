<?php

namespace App\Http\Controllers\Api\V1\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\AddressBookRequest;
use App\Http\Resources\Address\AddressBookResource;
use App\Services\Address\AddressBookService;
use App\Services\Applications\Api\ApiResponse;
use Illuminate\Http\Request;

class AddressBookController extends Controller
{
    public function __construct(protected AddressBookService $addressBookService) {}

    public function index(Request $request, $userId)
    {
        return $this->handleRequest(function () use ($request, $userId) {

            $data = $this->addressBookService->index($request->all() + ['user_id' => $userId]);

            return ApiResponse::success(AddressBookResource::collection($data));
        });
    }

    public function store(AddressBookRequest $request, $userId)
    {
        return $this->handleRequest(function () use ($request, $userId) {
            $data = $this->addressBookService->store($request->validated() + ['user_id' => $userId]);

            return ApiResponse::success(AddressBookResource::make($data), 'Address book entry created successfully', 201);
        });
    }

    public function show(Request $request, string $userId, string $id)
    {
        return $this->handleRequest(function () use ($request, $userId, $id) {
            $data = $this->addressBookService->show($request->all() + ['user_id' => $userId], $id);

            return ApiResponse::success(AddressBookResource::make($data));
        });
    }

    public function update(AddressBookRequest $request, string $userId, string $id)
    {
        return $this->handleRequest(function () use ($request, $userId, $id) {
            $data = $this->addressBookService->update($id, $request->validated() + ['user_id' => $userId]);

            return ApiResponse::success(AddressBookResource::make($data), 'Address book entry updated successfully');
        });
    }
}
