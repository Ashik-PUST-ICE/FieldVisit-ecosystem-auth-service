<?php

namespace App\Repositories\Eloquents;

use App\Models\MicroService;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use App\Services\Applications\ServiceTokenManager;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function generateToken(array $attributes): mixed
    {
        $token = app(ServiceTokenManager::class)->getToken($attributes['name']);

        return $token;
    }

    public function getServiceByName(string $name): mixed
    {
        return MicroService::where('name', $name)->first();
    }
}
