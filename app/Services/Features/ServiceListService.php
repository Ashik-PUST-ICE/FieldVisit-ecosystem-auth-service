<?php

namespace App\Services\Features;

use App\Repositories\Interfaces\ServiceRepositoryInterface;

class ServiceListService
{
    public function __construct(protected ServiceRepositoryInterface $serviceRepository) {}

    public function generateToken(array $credentials)
    {
        return $this->serviceRepository->generateToken($credentials);
    }
}
