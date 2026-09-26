<?php

namespace App\Http\Controllers\Api\V1\Modules\Clients;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Clients\ClientService;
use App\Models\Directory\UserNetworkIndex;
use App\Http\Requests\Address\AddressBookRequest;
use App\Http\Requests\Client\UserIdentityRequest;
use App\Http\Requests\Modules\Clients\PhoneBookRequest;
use App\Http\Requests\Modules\Clients\StoreClientRequest;
use App\Http\Requests\Modules\Clients\UpdateClientRequest;
use App\Http\Resources\Modules\Clients\Users\ClientResource;
use App\Http\Resources\Modules\Clients\Users\ClientListResource;
use App\Http\Resources\Modules\Clients\Users\ClientWithoutConnectionResource;

class ClientController extends Controller
{
    public function __construct(protected ClientService $clientService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->clientService->index($request->all());

            return ApiResponse::success(ClientListResource::collection($data));
        });
    }

    public function store(StoreClientRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->clientService->store($request->validated());

            return ApiResponse::success(ClientResource::make($data), 'Client created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->clientService->show($id);

            return ApiResponse::success(ClientResource::make($data));
        });
    }

    public function search(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->clientService->search($request->all());

            return ApiResponse::success(ClientListResource::collection($data));
        });
    }

    public function updateUser(UpdateClientRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $user = $this->clientService->updateUser($id, $request->validated());

            return ApiResponse::success(ClientResource::make($user), 'User information updated successfully');
        });
    }

    public function updateAddress(AddressBookRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $user = $this->clientService->updateAddress($id, $request->validated());

            return ApiResponse::success(ClientResource::make($user), 'Address updated successfully');
        });
    }

    public function updateIdentity(UserIdentityRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $user = $this->clientService->updateIdentity($id, $request->validated());

            return ApiResponse::success(ClientResource::make($user), 'Identity updated successfully');
        });
    }

    public function updatePhoneBooks(PhoneBookRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $user = $this->clientService->updatePhoneBooks($id, $request->validated());

            return ApiResponse::success(ClientResource::make($user), 'Phone books updated successfully');
        });
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'contact' => ['required', 'string'],
            'type' => ['required', 'in:sms,email,whatsapp'],
        ]);

        return $this->handleRequest(function () use ($request) {
            $data = $this->clientService->sendOtp($request->all());

            return ApiResponse::success($data);
        });
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'contact' => ['required', 'string'],
            'type' => ['required', 'in:sms,email,whatsapp'],
            'otp' => ['required', 'string'],
        ]);

        return $this->handleRequest(function () use ($request) {
            $data = $this->clientService->verifyOtp($request->all());

            return ApiResponse::success($data, 'OTP verified successfully');
        });
    }

    public function getClientsWithoutConnection(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->clientService->getClientsWithoutConnection($request->all());

            return ApiResponse::success(ClientWithoutConnectionResource::collection($data));
        });
    }






    public function getClientType(Request $request)
    {

        return $this->handleRequest(function () use ($request) {
            $baseQuery = UserNetworkIndex::with('user');

            $filteredQuery = $this->clientService->getClientType($baseQuery,$request->type);

            return ApiResponse::success(ClientListResource::collection($filteredQuery->get()));
        });
    }


    public function getClientInformation(Request $request, string $userId, string $networkId)
    {
        return $this->handleRequest(function () use ($userId, $networkId) {

            $data = $this->clientService->getClientInformation($userId, $networkId);

            return ApiResponse::success($data ,'Data Get successfully');
        });
    }

}
